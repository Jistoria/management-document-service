<?php

namespace App\Services;

use App\Jobs\CacheTokenValidation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TokenValidationService
{
    public function __construct(
        private AzureJwt $azureJwt,
        private GraphClient $graphClient
    ) {}

    /**
     * Valida un token con estrategia de cache inteligente
     */
    public function validateToken(
        string $token,
        array $requiredScopes = [],
        bool $forceValidation = false
    ): array {
        $tokenHash = hash('sha256', $token);
        $cacheKey = "jwt:validated:{$tokenHash}";

        // 1. Intentar obtener desde cache si no es forzado
        if (!$forceValidation) {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                $validationResult = json_decode($cached, true);

                // Verificar que los scopes requeridos estén presentes
                if ($this->hasSufficientScopes($validationResult['scopes'] ?? [], $requiredScopes)) {
                    Log::debug('Token validation retrieved from cache', [
                        'hash' => substr($tokenHash, 0, 8),
                        'user_id' => $validationResult['user_id'] ?? 'unknown'
                    ]);
                    return $validationResult;
                }
            }
        }

        // 2. Validación en tiempo real si no está en cache o es forzada
        try {
            $claims = $this->azureJwt->validate($token, $requiredScopes);

            $validationResult = [
                'claims' => $claims,
                'validated_at' => now()->toISOString(),
                'scopes' => $claims['scp'] ?? [],
                'user_id' => $claims['oid'] ?? null,
                'upn' => $claims['preferred_username'] ?? $claims['upn'] ?? null,
                'graph_validated' => false,
                'source' => 'realtime'
            ];

            // 3. Cachear resultado inmediatamente
            $this->cacheValidationResult($tokenHash, $validationResult, $claims);

            // 4. Disparar job asíncrono para validación completa con Graph si no está ya validado
            $this->scheduleAsyncValidation($token, $requiredScopes);

            return $validationResult;
        } catch (\Throwable $e) {
            Log::error('Token validation failed', [
                'error' => $e->getMessage(),
                'hash' => substr($tokenHash, 0, 8)
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene datos de usuario desde cache de Graph API
     */
    public function getCachedUserData(string $userOid): ?array
    {
        $graphCacheKey = "graph:user:{$userOid}";
        $cached = Redis::get($graphCacheKey);

        return $cached ? json_decode($cached, true) : null;
    }

    /**
     * Verifica si un token está pre-validado y es válido
     */
    public function isTokenPreValidated(string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $cacheKey = "jwt:validated:{$tokenHash}";

        return Redis::exists($cacheKey);
    }

    /**
     * Invalida cache de un token específico
     */
    public function invalidateToken(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        $cacheKey = "jwt:validated:{$tokenHash}";

        $cached = Redis::get($cacheKey);
        if ($cached) {
            $data = json_decode($cached, true);
            $userOid = $data['user_id'] ?? null;

            // Eliminar cache del token
            Redis::del($cacheKey);

            // Eliminar cache por usuario si existe
            if ($userOid) {
                Redis::del("jwt:user:{$userOid}");
                Redis::del("graph:user:{$userOid}");
            }

            Log::info('Token cache invalidated', [
                'hash' => substr($tokenHash, 0, 8),
                'user_id' => $userOid
            ]);
        }
    }

    /**
     * Pre-carga validación de token en background
     */
    public function preloadTokenValidation(string $token, array $requiredScopes = []): void
    {
        if (!$this->isTokenPreValidated($token)) {
            CacheTokenValidation::dispatch($token, $requiredScopes, true)
                ->onQueue('auth-cache');

            Log::debug('Token validation preload scheduled', [
                'hash' => substr(hash('sha256', $token), 0, 8)
            ]);
        }
    }

    /**
     * Obtiene estadísticas del cache de tokens
     */
    public function getCacheStats(): array
    {
        $pattern = 'jwt:validated:*';
        $keys = Redis::keys($pattern);

        $stats = [
            'total_cached_tokens' => count($keys),
            'cache_hit_rate' => 0, // Se podría implementar con contadores
            'oldest_cache' => null,
            'newest_cache' => null
        ];

        if (!empty($keys)) {
            $ttls = array_map(fn($key) => Redis::ttl($key), $keys);
            $stats['average_ttl'] = array_sum($ttls) / count($ttls);
        }

        return $stats;
    }

    private function hasSufficientScopes(array $tokenScopes, array $requiredScopes): bool
    {
        if (empty($requiredScopes)) {
            return true;
        }

        return count(array_intersect($requiredScopes, $tokenScopes)) > 0;
    }

    private function cacheValidationResult(string $tokenHash, array $validationResult, array $claims): void
    {
        $cacheKey = "jwt:validated:{$tokenHash}";

        // Determinar TTL
        $now = time();
        $exp = $claims['exp'] ?? ($now + 3600);
        $ttl = min($exp - $now, 3600);

        if ($ttl > 0) {
            Redis::setex($cacheKey, $ttl, json_encode($validationResult));

            // Cache adicional por user OID
            if (!empty($claims['oid'])) {
                $userCacheKey = "jwt:user:{$claims['oid']}";
                Redis::setex($userCacheKey, $ttl, $tokenHash);
            }
        }
    }

    private function scheduleAsyncValidation(string $token, array $requiredScopes): void
    {
        // Solo programar validación async si no está ya programada
        CacheTokenValidation::dispatch($token, $requiredScopes, true)
            ->delay(now()->addSeconds(5)) // Pequeño delay para no bloquear la respuesta
            ->onQueue('auth-cache');
    }
}