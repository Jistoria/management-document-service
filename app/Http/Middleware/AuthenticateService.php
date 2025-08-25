<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redis;

class AuthenticateService
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Falta el token'], 401);
        }

        // calcula el hash SHA‑256 del token
        $tokenHash = hash('sha256', $token);
        $cacheKey  = "jwt:validated:$tokenHash";

        // verifica en Redis si ya fue validado por el auth-service
        if (!Redis::exists($cacheKey)) {
            // opcional: puedes llamar al endpoint de validación del auth-service
            // o devolver un 401 inmediato
            return response()->json(['message' => 'Token no validado'], 401);
        }

        // almacena el hash y el token en la request para el middleware de roles
        $request->attributes->set('token_hash', $tokenHash);

        return $next($request);
    }
}
