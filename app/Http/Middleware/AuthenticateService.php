<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class AuthenticateService
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Falta el token'], 401);
        }

        // Detectar tipo de token basado en su estructura
        $tokenType = $this->detectTokenType($token);
        $tokenHash = hash('sha256', $token);

        // Obtener clave de cache según el tipo de token
        $cacheKey = $this->getCacheKey($tokenType, $tokenHash);

        Log::info('[AuthenticateService] Validating token', [
            'type' => $tokenType,
            'hash' => substr($tokenHash, 0, 8),
            'cache_key' => $cacheKey
        ]);

        // Verificar en Redis si ya fue validado por el auth-service
        $validationData = Redis::connection('default')->get($cacheKey);
        if (!$validationData) {
            Log::warning('[AuthenticateService] Token not found in cache', [
                'type' => $tokenType,
                'hash' => substr($tokenHash, 0, 8)
            ]);
            return response()->json(['message' => 'Token no validado o expirado'], 401);
        }

        $tokenValidation = json_decode($validationData, true);
        if (!$tokenValidation) {
            return response()->json(['message' => 'Datos de validación corruptos'], 401);
        }

        // Verificar que el token no haya expirado
        if (isset($tokenValidation['expires_at'])) {
            $expiresAt = \Carbon\Carbon::parse($tokenValidation['expires_at']);
            if ($expiresAt->isPast()) {
                Log::warning('[AuthenticateService] Token expired', [
                    'expires_at' => $tokenValidation['expires_at'],
                    'hash' => substr($tokenHash, 0, 8)
                ]);
                return response()->json(['message' => 'Token expirado'], 401);
            }
        }

        // Almacenar datos de validación en la request para el middleware de roles
        $request->attributes->set('token_hash', $tokenHash);
        $request->attributes->set('token_type', $tokenType);
        $request->attributes->set('token_validation', $tokenValidation);

        Log::info('[AuthenticateService] Token validated successfully', [
            'type' => $tokenType,
            'user_id' => $tokenValidation['user_id'] ?? 'unknown',
            'hash' => substr($tokenHash, 0, 8)
        ]);

        return $next($request);
    }

    /**
     * Detecta el tipo de token basado en su estructura
     */
    private function detectTokenType(string $token): string
    {
        // Si es un JWT (tiene 3 partes separadas por puntos)
        if (substr_count($token, '.') === 2) {
            try {
                $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $token)[1])), true);

                // Si tiene 'iss' de Microsoft, es Azure/MS
                if (isset($payload['iss']) && str_contains($payload['iss'], 'microsoft')) {
                    return 'azure';
                }

                // Si tiene 'scp' o 'roles', probablemente es Azure/MS
                if (isset($payload['scp']) || isset($payload['roles'])) {
                    return 'microservice';
                }
            } catch (\Throwable) {
                // Si falla el parsing, asumir que es local
            }
        }

        // Por defecto, asumir que es un token local (Passport)
        return 'local';
    }

    /**
     * Obtiene la clave de cache según el tipo de token
     */
    private function getCacheKey(string $tokenType, string $tokenHash): string
    {
        return match ($tokenType) {
            'local' => "laravel_database_local_token:{$tokenHash}",
            'azure' => "laravel_database_azure_token:{$tokenHash}",
            'microservice' => "laravel_database_ms_token:{$tokenHash}",
            default => "laravel_database_local_token:{$tokenHash}"
        };
    }
}
