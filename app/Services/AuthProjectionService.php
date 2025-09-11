<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class AuthProjectionService
{
    public function upsertUserFromSnapshot(?string $tenantId, array $snap): void
    {
        $userId = $snap['id'] ?? null;
        if (!$userId) return;

        DB::table('md_auth_users')->upsert([[
            'tenant_id'      => $tenantId,
            'user_id'        => $userId,
            'name'           => $snap['name']        ?? null,
            'email'          => $snap['email']       ?? null,
            'status'         => $snap['status']      ?? null,
            'deleted_at'     => $snap['deleted_at']  ?? null,
            'updated_at_src' => $snap['updated_at']  ?? null,
            'updated_at'     => now(),
        ]], ['tenant_id', 'user_id'], ['name', 'email', 'status', 'deleted_at', 'updated_at_src', 'updated_at']);
    }

    public function upsertUserFromObserver(?string $tenantId, array $after, array $before): void
    {
        $this->upsertUserFromSnapshot($tenantId, [
            'id'         => $after['id']         ?? $before['id'] ?? null,
            'name'       => $after['name']       ?? null,
            'email'      => $after['email']      ?? null,
            'status'     => $after['status']     ?? null,
            'deleted_at' => $after['deleted_at'] ?? null,
            'updated_at' => now()->toISOString(),
        ]);
    }

    public function attachPermissions(?string $tenantId, ?string $userId, array $perms, ?string $grantedBy, ?string $reason): void
    {
        if (!$tenantId || !$userId) return;

        $slugs = array_values(array_unique(array_filter(array_map(fn($p) => $p['slug'] ?? null, $perms))));
        if (!$slugs) return;

        // Catálogo local
        DB::table('md_auth_permissions')->upsert(
            array_map(fn($s) => ['permission_slug' => $s], $slugs),
            ['permission_slug']
        );

        // Asignaciones
        $now = now();
        foreach ($slugs as $slug) {
            DB::table('md_auth_user_permissions')->upsert([[
                'tenant_id'       => $tenantId,
                'user_id'         => $userId,
                'permission_slug' => $slug,
                'granted_by'      => $grantedBy,
                'reason'          => $reason,
                'created_at'      => $now,
            ]], ['tenant_id', 'user_id', 'permission_slug'], ['granted_by', 'reason']);
        }
    }

    public function detachPermissions(?string $tenantId, ?string $userId, array $perms): void
    {
        $slugs = array_values(array_unique(array_filter(array_map(fn($p) => $p['slug'] ?? null, $perms))));
        if ($tenantId && $userId && $slugs) {
            DB::table('md_auth_user_permissions')
                ->where('tenant_id', $tenantId)
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
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->update(['deleted_at' => now(), 'updated_at' => now()]);
    }
}
