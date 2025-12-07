<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class AuthProjectionService
{
    
    public function upsertUserFromSnapshot(?string $tenantId, array $snap): void
    {
        $userId = $snap['id'] ?? null;
        if (!$userId) return;

        // Sanitización de nulos
        $tenantId = empty($tenantId) ? null : $tenantId;

        DB::table('md_auth_users')->upsert([
            [
                'user_id'        => $userId,
                'tenant_id'      => $tenantId, // Ahora es un campo de datos normal
                'name'           => $snap['name']        ?? null,
                'email'          => $snap['email']       ?? null,
                'status'         => $snap['status']      ?? null,
                'guid_ms'        => $snap['guid_ms']     ?? null,
                'deleted_at'     => $snap['deleted_at']  ?? null,
                'updated_at_src' => $snap['updated_at']  ?? null,
                'updated_at'     => now(),
            ]
        ], 
        // ------------------------------------------------------------------
        // CORRECCIÓN AQUÍ: La llave única ahora es solo 'user_id'
        // ------------------------------------------------------------------
        ['user_id'], 
        
        // Columnas a actualizar si el usuario ya existe
        [
            'tenant_id', // Agregado: actualizar tenant si cambia
            'name', 
            'email', 
            'status', 
            'guid_ms', 
            'deleted_at', 
            'updated_at_src', 
            'updated_at'
        ]);
    }

    public function upsertUserFromObserver(?string $tenantId, array $after, array $before): void
    {
        $this->upsertUserFromSnapshot($tenantId, [
            'id'         => $after['id']         ?? $before['id'] ?? null,
            'name'       => $after['name']       ?? null,
            'email'      => $after['email']      ?? null,
            'guid_ms'    => $after['guid_ms']    ?? null,
            'status'     => $after['status']     ?? null,
            'deleted_at' => $after['deleted_at'] ?? null,
            'updated_at' => now()->toISOString(),
        ]);
    }

    public function attachPermissions(?string $tenantId, ?string $userId, array $perms, ?string $grantedBy, ?string $reason): void
    {
        // CORRECCIÓN 1: Permitir tenant_id nulo (solo validar userId)

        Log::info('AttachPermissions called', ['tenantId' => $tenantId, 'userId' => $userId, 'permissions' => $perms, 'grantedBy' => $grantedBy, 'reason' => $reason]);

        if (!$userId) return;
        
        // Sanitizar string vacío a NULL
        $tenantId = empty($tenantId) ? null : $tenantId;

        // Extraer slugs limpios
        $slugs = array_values(array_unique(array_filter(array_map(fn($p) => $p['slug'] ?? null, $perms))));
        
        if (empty($slugs)) return;

        // 1. Asegurar catálogo (Upsert simple)
        DB::table('md_auth_permissions')->upsert(
            array_map(fn($s) => ['permission_slug' => $s], $slugs),
            ['permission_slug']
        );

        // 2. Asignaciones (Upsert con lógica NULL safe)
        $now = now();
        $records = [];
        
        foreach ($slugs as $slug) {
            $records[] = [
                'tenant_id'       => $tenantId,
                'user_id'         => $userId,
                'permission_slug' => $slug,
                'granted_by'      => $grantedBy,
                'reason'          => $reason,
                'created_at'      => $now,
            ];
        }

        // CORRECCIÓN 2: Upsert usando el nuevo índice único
        DB::table('md_auth_user_permissions')->upsert(
            $records,
            // Postgres usará el índice único (user_id, permission_slug, tenant_id) automáticamente
            ['user_id', 'permission_slug', 'tenant_id'], 
            ['granted_by', 'reason', 'created_at']
        );
    }

    public function detachPermissions(?string $tenantId, ?string $userId, array $perms): void
    {
        // Sanitizar y validar
        $userId = $userId ?: null;
        $tenantId = empty($tenantId) ? null : $tenantId; // Convertir "" a NULL
        
        $slugs = array_values(array_unique(array_filter(array_map(fn($p) => $p['slug'] ?? null, $perms))));
        
        if ($userId && !empty($slugs)) {
            $query = DB::table('md_auth_user_permissions')
                ->where('user_id', $userId)
                ->whereIn('permission_slug', $slugs);

            // Manejo explícito de NULL para SQL
            if (is_null($tenantId)) {
                $query->whereNull('tenant_id');
            } else {
                $query->where('tenant_id', $tenantId);
            }

            $query->delete();
        }
    }

    public function markUserDeleted(?string $tenantId, array $after, array $before): void
    {
        $userId = $after['id'] ?? $before['id'] ?? null;
        if (!$userId) return;

        DB::table('md_auth_users')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->update(['deleted_at' => now(), 'updated_at' => now()]);
    }
}
