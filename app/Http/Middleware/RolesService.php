<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RolesService
{
    public function __construct(private string $requiredPermission = 'document.view') {}

    public function handle(Request $request, Closure $next, string $requiredPermission = null)
    {
        $this->requiredPermission = $requiredPermission ?? $this->requiredPermission;

        $tokenHash = $request->attributes->get('token_hash');
        if (!$tokenHash) {
            return response()->json(['message' => 'Falta token hash'], 401);
        }

        // 1) obtener ID o CODE desde headers
        $microId   = $request->header('X-Microservice-ID');   // UUID
        $microCode = $request->header('X-Microservice-Code'); // code legible

        if (!$microId && !$microCode) {
            return response()->json(['message' => 'Falta X-Microservice-ID o X-Microservice-Code'], 400);
        }

        // 2) leer ÚNICA sesión raíz
        $sessionKey = "ms:session:$tokenHash";
        $raw        = Redis::get($sessionKey);
        if (!$raw) {
            return response()->json(['message' => 'Sesión no encontrada'], 403);
        }

        $session = json_decode($raw, true) ?: [];
        Log::info($sessionKey);
        Log::info($session);

        // 3) localizar bloque del microservicio
        $entry = null;

        if ($microId && isset($session['microservices_by_id'][$microId])) {
            $entry = $session['microservices_by_id'][$microId];
        } elseif ($microCode && isset($session['microservices_by_code'][$microCode])) {
            $entry = $session['microservices_by_code'][$microCode];
            // si vino por code, fija el ID como atributo útil aguas abajo
            if (isset($entry['id'])) {
                $microId = $entry['id'];
            }
        }

        if (!$entry) {
            return response()->json(['message' => 'Microservicio no autorizado en sesión'], 403);
        }

        // 4) validar permiso requerido
        $permissions = $entry['permissions'] ?? [];
        if (!in_array($this->requiredPermission, $permissions, true)) {
            return response()->json(['message' => 'Sin permiso'], 403);
        }

        // 5) propagar datos útiles
        $request->attributes->set('user_id', $session['user_id'] ?? null);
        $request->attributes->set('microservice_id', $microId);
        $request->attributes->set('microservice_code', $entry['code'] ?? null);

        return $next($request);
    }
}
