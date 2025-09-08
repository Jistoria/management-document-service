<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;


class AuthenticateService
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Falta el token'], 401);
        }

        $hash = hash('sha256', $token);
        $session = null;

        $data = Redis::get("laravel_database_session:{$hash}");
        if ($data) {
            $session = json_decode($data, true);
        }

        if (!$session) {
            return response()->json(['message' => 'Token no validado o expirado'], 401);
        }

        Log::debug('[AuthenticateService] Session data retrieved', $session);

        $request->attributes->set('token_hash', $hash);
        $request->attributes->set('token_validation', $token);
        $request->attributes->set('session', $session);

        Log::debug('token hash: ' . $hash);
        return $next($request);
    }
}
