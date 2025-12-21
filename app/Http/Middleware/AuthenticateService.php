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

        $decodedToken = null;
        $tokenType = $this->detectTokenType($token);

        Log::info("[AuthenticateService] Token detectado", ['type' => $tokenType]);

        // ---------------------------------------------------------
        // PASO 1: Validación Criptográfica según tipo de token
        // ---------------------------------------------------------
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

        // ---------------------------------------------------------
        // PASO 2: Enriquecimiento con Redis
        // ---------------------------------------------------------
        $hash = hash('sha256', $token);
        $session = null;
        $source = 'token_fallback';

        try {
            // Intenta leer sesión de Redis (solo loguea error si falla la conexión)
            $redisData = Redis::get("laravel_database_session:{$hash}");
            Log::info("[AuthenticateService] Sesión obtenida: " . ($redisData ? '' : '❌ No encontrada en Redis'));
            if ($redisData) {
                $session = json_decode($redisData, true);
                $source = 'redis_cache';
                
                // Extraer permisos del microservicio management-document-service
                $msData = $session['microservices_data']['by_code']['management-document-service'] ?? null;
                if ($msData) {
                    $redisPermissions = $msData['permissions'] ?? [];
                    Log::info("[AuthenticateService] Permisos encontrados en Redis", [
                        'count' => count($redisPermissions),
                        'permissions' => $redisPermissions
                    ]);
                    // Inyectar permisos en el request para uso de fallback
                    $request->attributes->set('redis_permissions', $redisPermissions);
                }
            }
        } catch (Exception $e) {
            Log::error('[AuthenticateService] Redis no disponible: ' . $e->getMessage());
        }

        // ---------------------------------------------------------
        // PASO 3: Fusión y Fallback
        // ---------------------------------------------------------
        if (!$session) {
            // Si Redis falla o no tiene la sesión, usamos los datos del JWT validado
            $session = [
                'user_id' => $decodedToken->sub ?? null,
                'scopes'  => $decodedToken->scopes ?? [],
                // Mapea otros campos necesarios que vengan en tu token
            ];
        }

        // Inyectar atributos al request
        $request->attributes->set('token_hash', $hash);
        $request->attributes->set('jwt_payload', $decodedToken);
        $request->attributes->set('session', $session);
        $request->attributes->set('auth_source', $source);

        

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