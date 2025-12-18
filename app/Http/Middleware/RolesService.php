<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ApiResponse;

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

        // 3. Fallback a Redis si Kafka está caído (no hay permisos en BD)
        if (!$hasPermission) {
            $redisPermissions = $request->attributes->get('redis_permissions', []);
            
            if (!empty($redisPermissions)) {
                $requiredPermission = 'md.' . $permission;
                $hasPermission = in_array($requiredPermission, $redisPermissions);
                
                if ($hasPermission) {
                    Log::info('[RolesService] Permiso validado desde Redis (Kafka fallback)', [
                        'user_id'    => $userId,
                        'permission' => $requiredPermission
                    ]);
                    
                    // Sincronizar permisos de Redis a BD en background
                    $this->syncRedisPermissionsToDB($userId, $redisPermissions);
                }
            }
        }

        if (!$hasPermission) {
            Log::warning('[RolesService] Acceso Denegado', [
                'user_id'    => $userId,
                'permission' => $permission,
                'checked_redis' => !empty($request->attributes->get('redis_permissions', []))
            ]);

            return ApiResponse::error('No tienes permisos para realizar esta acción.', 403);
        }

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

    /**
     * Sincroniza permisos de Redis a la base de datos local.
     * Se ejecuta en background cuando se usa fallback de Redis.
     */
    private function syncRedisPermissionsToDB(string $userId, array $redisPermissions): void
    {
        try {
            // Usar el servicio de proyección para sincronizar
            $authProjection = app(\App\Services\AuthProjectionService::class);
            
            // Convertir array de strings a array de objetos con 'slug'
            $permissionsArray = array_map(fn($slug) => ['slug' => $slug], $redisPermissions);
            
            // Sincronizar sin tenant (null), con metadata de fallback
            $authProjection->attachPermissions(
                tenantId: null,
                userId: $userId,
                perms: $permissionsArray,
                grantedBy: null, // granted_by es UUID, dejarlo nulo para fallback
                reason: 'Sincronización automática desde Redis (Kafka caído)'
            );
            
            Log::info('[RolesService] Permisos sincronizados desde Redis a BD', [
                'user_id' => $userId,
                'count' => count($redisPermissions)
            ]);
            
        } catch (\Exception $e) {
            // No fallar la request si la sincronización falla
            Log::error('[RolesService] Error al sincronizar permisos desde Redis', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }
}