<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasAuditFields
 *
 * Proporciona funcionalidad de auditoría para modelos que tienen campos
 * created_by, updated_by y version para integración con microservicios
 */
trait HasAuditFields
{
    /**
     * Boot the trait
     */
    protected static function bootHasAuditFields()
    {
        // Automatically set created_by and version on creation
        static::creating(function (Model $model) {
            if (empty($model->created_by)) {
                $model->created_by = $model->getCurrentExternalUserId();
            }
            if (empty($model->version)) {
                $model->version = 1;
            }
        });

        // Automatically set updated_by and increment version on update
        static::updating(function (Model $model) {
            $model->updated_by = $model->getCurrentExternalUserId();
            if ($model->isDirty() && !$model->isDirty('version')) {
                $model->version = ($model->version ?? 1) + 1;
            }
        });
    }

    /**
     * Get the current external user ID from request context
     * This should be set by middleware from JWT token or API authentication
     */
    protected static function getCurrentExternalUserId(): ?string
    {
        // In a real microservice, this would come from:
        // - JWT token payload
        // - Request header (X-User-ID)
        // - Service authentication context
        return request()->header('X-User-ID') ?? 'system';
    }

    /**
     * Scope to filter by external user
     */
    public function scopeCreatedBy($query, string $externalUserId)
    {
        return $query->where('created_by', $externalUserId);
    }

    /**
     * Scope to filter by version
     */
    public function scopeVersion($query, int $version)
    {
        return $query->where('version', $version);
    }

    /**
     * Get the latest version of this model
     */
    public function getLatestVersion()
    {
        return static::where($this->getKeyName(), $this->getKey())
            ->orderBy('version', 'desc')
            ->first();
    }
}
