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

        DB::table('md_auth_users')->upsert([
            [
                'user_id'        => $userId,
                'name'           => $snap['name']        ?? null,
                'email'          => $snap['email']       ?? null,
                'status'         => $snap['status']      ?? null,
                'guid_ms'        => $snap['guid_ms']     ?? null,
                'deleted_at'     => $snap['deleted_at']  ?? null,
                'updated_at_src' => $snap['updated_at']  ?? null,
                'updated_at'     => now(),
            ]
        ], 
        ['user_id'], 
        [
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
        Log::info('AttachPermissions called', ['userId' => $userId, 'permissions' => $perms, 'grantedBy' => $grantedBy, 'reason' => $reason]);

        if (!$userId) return;

        // Extraer slugs limpios
        $slugs = array_values(array_unique(array_filter(array_map(fn($p) => $p['slug'] ?? null, $perms))));
        
        if (empty($slugs)) return;

        // 1. Asegurar catálogo (Upsert simple)
        DB::table('md_auth_permissions')->upsert(
            array_map(fn($s) => ['permission_slug' => $s], $slugs),
            ['permission_slug']
        );

        // 2. Asignaciones (Upsert simplificado sin tenant_id)
        $now = now();
        $records = [];
        
        foreach ($slugs as $slug) {
            $records[] = [
                'user_id'         => $userId,
                'permission_slug' => $slug,
                'granted_by'      => $grantedBy,
                'reason'          => $reason,
                'created_at'      => $now,
            ];
        }

        DB::table('md_auth_user_permissions')->upsert(
            $records,
            ['user_id', 'permission_slug'], 
            ['granted_by', 'reason', 'created_at']
        );
    }

    public function detachPermissions(?string $tenantId, ?string $userId, array $perms): void
    {
        if (!$userId) return;
        
        $slugs = array_values(array_unique(array_filter(array_map(fn($p) => $p['slug'] ?? null, $perms))));
        
        if (!empty($slugs)) {
            DB::table('md_auth_user_permissions')
                ->where('user_id', $userId)
                ->whereIn('permission_slug', $slugs)
                ->delete();
        }
    }

    public function markUserDeleted(?string $tenantId, array $after, array $before): void
    {
        $userId = $after['id'] ?? $before['id'] ?? null;
        if (!$userId) return;

        DB::table('md_auth_users')
            ->where('user_id', $userId)
            ->update(['deleted_at' => now(), 'updated_at' => now()]);
    }
}
