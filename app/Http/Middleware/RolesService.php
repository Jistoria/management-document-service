<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolesService
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission  El slug del permiso requerido (ej: 'documents.create')
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        // 1. Obtener Identidad (Inyectada por AuthenticateService)
        // Ya no consultamos Redis aquí; confiamos en el middleware anterior.
        $session = $request->attributes->get('session');
        // Fallback: Si la sesión viene vacía (raro si pasó Auth), intentamos sacar el ID del JWT decodificado
        $userId = $session['user_id'] 
                  ?? $request->attributes->get('jwt_payload')?->sub 
                  ?? null;

        if (!$userId) {
            Log::warning('[RolesService] No se pudo identificar al usuario en el request.');
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Forzar a string para consistencia
        $userId = (string) $userId;

        // 2. Validación de Permisos (Contra la Tabla Espejo Local)
        $hasPermission = $this->checkLocalPermission($userId, $permission);

        if (!$hasPermission) {
            Log::warning('[RolesService] Acceso Denegado', [
                'user_id'    => $userId,
                'permission' => $permission
            ]);

            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción.',
                'required' => $permission
            ], 403);
        }

        // Log de éxito (opcional, útil para debug)
        Log::debug('[RolesService] Acceso Permitido', [
            'user' => $userId, 
            'perm' => $permission
        ]);

        return $next($request);
    }

    /**
     * Verifica si existe el permiso en la tabla de proyección local.
     */
    private function checkLocalPermission(string $userId, string $permission): bool
    {
        // Consultamos la tabla espejo que llenamos con Kafka
        return DB::table('md_auth_user_permissions')
            ->where('user_id', $userId)
            ->where('permission_slug', 'md.'.$permission)
            ->exists();
    }
}