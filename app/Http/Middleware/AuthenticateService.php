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

        // ---------------------------------------------------------
        // PASO 1: Validación Criptográfica (Usando librería JWK)
        // ---------------------------------------------------------
        try {
            // Cacheamos las JWKS crudas (el JSON)
            $jwks = cache()->remember('auth-jwks', 36000, function () {
                $url = config('auth.jwks_url'); // Asegúrate que esto cargue 'http://auth-nginx/.well-known/jwks.json'
                $response = Http::timeout(5)->get($url);
                
                if ($response->failed()) {
                    throw new Exception("No se pudieron descargar las JWKS del Auth Service");
                }
                return $response->json();
            });

            // Usamos la librería para parsear el JSON a objetos Key válidos
            // Esto maneja automáticamente la conversión de Modulus/Exponent a PEM
            $publicKeys = JWK::parseKeySet($jwks);
            
            // Asumiendo que buscamos la llave 'passport-v1'
            // Ojo: Si rota la llave, parseKeySet devuelve un array indexado por 'kid'
            $kid = 'passport-v1';

            if (!isset($publicKeys[$kid])) {
                Log::error("[AuthenticateService] KID '$kid' no encontrado en JWKS.");
                return response()->json(['message' => 'Llave de seguridad no encontrada'], 401);
            }

            // Validar firma
            // $publicKeys[$kid] ya es un objeto Key listo para usar (en versiones recientes de php-jwt)
            $decodedToken = JWT::decode($token, $publicKeys[$kid]);

        } catch (Exception $e) {
            Log::warning("[AuthenticateService] Token rechazado: " . $e->getMessage());
            return response()->json([
                'message' => 'Token inválido', 
                'error' => $e->getMessage() // Útil para debug, quitar en prod
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
            if ($redisData) {
                $session = json_decode($redisData, true);
                $source = 'redis_cache';
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
}