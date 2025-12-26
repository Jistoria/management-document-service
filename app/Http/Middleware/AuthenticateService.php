<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;

class AuthenticateService
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Obtener Token
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Falta el token'], 401);
        }

        $hash = hash('sha256', $token);
        $session = null;
        $tokenType = null;
        $decodedToken = null;

        // ---------------------------------------------------------
        // PASO 1: Intentar obtener sesión desde Redis (ya validada por auth-service)
        // ---------------------------------------------------------
        try {
            $redisData = Redis::get("laravel_database_session:{$hash}");
            
            if ($redisData) {
                $session = json_decode($redisData, true);
                $tokenType = $session['token_type'] ?? null;
                
                Log::info("[AuthenticateService] ✅ Token encontrado en Redis", [
                    'token_type' => $tokenType,
                    'user_id' => $session['user_id'] ?? 'unknown',
                    'source' => 'redis_cache'
                ]);

                // Extraer permisos del microservicio management-document-service
                $msData = $session['microservices_data']['by_code']['management-document-service'] ?? null;
                if ($msData) {
                    $redisPermissions = $msData['permissions'] ?? [];
                    Log::info("[AuthenticateService] Permisos encontrados en Redis", [
                        'count' => count($redisPermissions),
                        'permissions' => $redisPermissions
                    ]);
                    $request->attributes->set('redis_permissions', $redisPermissions);
                }

                // Decodificar payload del token para uso interno (sin validar firma, ya fue validado)
                try {
                    $parts = explode('.', $token);
                    if (count($parts) === 3) {
                        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                        $decodedToken = (object) $payload;
                    }
                } catch (\Exception $e) {
                    Log::warning('[AuthenticateService] No se pudo decodificar payload del token');
                }

                // Inyectar atributos al request
                $request->attributes->set('token_hash', $hash);
                $request->attributes->set('jwt_payload', $decodedToken);
                $request->attributes->set('session', $session);
                $request->attributes->set('auth_source', 'redis_cache');
                $request->attributes->set('token_type', $tokenType);

                return $next($request);
            }

            Log::warning("[AuthenticateService] ⚠️ Token NO encontrado en Redis - Fallback a validación criptográfica");

        } catch (Exception $e) {
            Log::error('[AuthenticateService] Error al consultar Redis: ' . $e->getMessage());
        }

        // ---------------------------------------------------------
        // PASO 2: Fallback - Validación Criptográfica (si no está en Redis)
        // ---------------------------------------------------------
        $tokenType = $this->detectTokenType($token);
        Log::info("[AuthenticateService] Validando token manualmente", ['type' => $tokenType]);

        try {
            if ($tokenType === 'microsoft') {
                $decodedToken = $this->validateMicrosoftToken($token);
                Log::info("[AuthenticateService] Token de Microsoft validado exitosamente");
            } else {
                $decodedToken = $this->validatePassportToken($token);
                Log::info("[AuthenticateService] Token de Passport validado exitosamente");
            }
        } catch (Exception $e) {
            Log::warning("[AuthenticateService] Token rechazado", [
                'type' => $tokenType,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Token inválido', 
                'error' => $e->getMessage()
            ], 401);
        }

        // Construir sesión mínima desde el token decodificado
        $session = [
            'user_id' => $decodedToken->sub ?? $decodedToken->oid ?? null,
            'scopes'  => $decodedToken->scopes ?? [],
            'token_type' => $tokenType,
        ];

        // Inyectar atributos al request
        $request->attributes->set('token_hash', $hash);
        $request->attributes->set('jwt_payload', $decodedToken);
        $request->attributes->set('session', $session);
        $request->attributes->set('auth_source', 'token_fallback');
        $request->attributes->set('token_type', $tokenType);

        return $next($request);
    }

    /**
     * Detecta el tipo de token basándose en el issuer
     */
    private function detectTokenType(string $token): string
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return 'passport';
            }
            
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            
            // Detectar por issuer
            $issuer = $payload['iss'] ?? '';
            if (str_contains($issuer, 'login.microsoftonline.com')) {
                return 'microsoft';
            }
        } catch (\Exception $e) {
            Log::warning('[AuthenticateService] Error detectando tipo de token: ' . $e->getMessage());
        }
        
        return 'passport';
    }

    /**
     * Valida un token de Microsoft Azure AD
     */
    private function validateMicrosoftToken(string $token): object
    {
        $tenantId = config('auth.microsoft_tenant_id');
        
        if (!$tenantId) {
            throw new Exception("AZURE_TENANT_ID no configurado");
        }

        // Cachear las JWKS de Microsoft
        $jwks = cache()->remember('microsoft-jwks', 36000, function () use ($tenantId) {
            $url = "https://login.microsoftonline.com/{$tenantId}/discovery/v2.0/keys";
            
            Log::info("[AuthenticateService] Descargando JWKS de Microsoft", ['url' => $url]);
            
            $response = Http::timeout(10)->get($url);
            
            if ($response->failed()) {
                throw new Exception("No se pudieron descargar las JWKS de Microsoft");
            }
            
            return $response->json();
        });

        $publicKeys = JWK::parseKeySet($jwks);
        
        // Microsoft tokens tienen su propio kid en el header
        $header = json_decode(base64_decode(strtr(explode('.', $token)[0], '-_', '+/')), true);
        $kid = $header['kid'] ?? null;
        
        if (!$kid) {
            throw new Exception("Token de Microsoft sin KID");
        }
        
        if (!isset($publicKeys[$kid])) {
            throw new Exception("KID de Microsoft '{$kid}' no encontrado en JWKS");
        }
        
        // Validar el token con la clave pública de Microsoft
        $decoded = JWT::decode($token, $publicKeys[$kid]);
        
        // Validaciones adicionales de Azure AD
        $this->validateAzureAdClaims($decoded);
        
        return $decoded;
    }

    /**
     * Valida un token de Passport (auth-service local)
     */
    private function validatePassportToken(string $token): object
    {
        // Cacheamos las JWKS crudas (el JSON)
        $jwks = cache()->remember('auth-jwks', 36000, function () {
            $url = config('auth.jwks_url');
            
            Log::info("[AuthenticateService] Descargando JWKS de Auth Service", ['url' => $url]);
            
            $response = Http::timeout(5)->get($url);
            
            if ($response->failed()) {
                throw new Exception("No se pudieron descargar las JWKS del Auth Service");
            }
            return $response->json();
        });

        // Usamos la librería para parsear el JSON a objetos Key válidos
        $publicKeys = JWK::parseKeySet($jwks);
        
        // Buscar la llave 'passport-v1'
        $kid = 'passport-v1';

        if (!isset($publicKeys[$kid])) {
            throw new Exception("KID 'passport-v1' no encontrado en JWKS");
        }

        // Validar firma
        return JWT::decode($token, $publicKeys[$kid]);
    }

    /**
     * Validaciones adicionales específicas de Azure AD
     */
    private function validateAzureAdClaims(object $decoded): void
    {
        $allowedAzp = config('auth.azure_allowed_azp');
        
        // Validar azp (authorized party) si está configurado
        if ($allowedAzp && isset($decoded->azp)) {
            if ($decoded->azp !== $allowedAzp) {
                throw new Exception("Token de Azure con azp no autorizado");
            }
        }
        
        // Validar que el token no haya expirado
        if (isset($decoded->exp) && $decoded->exp < time()) {
            throw new Exception("Token de Azure expirado");
        }
        
        Log::info("[AuthenticateService] Claims de Azure validados", [
            'azp' => $decoded->azp ?? 'N/A',
            'oid' => $decoded->oid ?? 'N/A',
            'tid' => $decoded->tid ?? 'N/A'
        ]);
    }
}