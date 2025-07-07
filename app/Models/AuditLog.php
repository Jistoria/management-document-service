<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use HasUuids;

    protected $table = 'audit_logs';

    protected $fillable = [
        'table_name',
        'record_id',
        'action',
        'user_id',
        'external_user_id',
        'user_email',
        'user_name',
        'ip_address',
        'user_agent',
        'service_name',
        'service_version',
        'endpoint',
        'correlation_id',
        'session_id',
        'old_values',
        'new_values',
        'changed_fields',
        'record_version_before',
        'record_version_after',
        'change_reason',
        'change_metadata',
        'business_context',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'change_metadata' => 'array',
        'business_context' => 'array',
        'created_at' => 'datetime',
        'ip_address' => 'string', // Laravel automáticamente maneja INET
    ];

    /**
     * Scope para filtrar por tabla
     */
    public function scopeForTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('external_user_id', $userId);
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para cambios recientes
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Obtener el modelo auditado (si existe)
     */
    public function getAuditedModelAttribute()
    {
        $modelClass = $this->getModelClassFromTable($this->table_name);

        if ($modelClass && class_exists($modelClass)) {
            return $modelClass::find($this->record_id);
        }

        return null;
    }

    /**
     * Mapear nombre de tabla a clase de modelo
     */
    protected function getModelClassFromTable(string $tableName): ?string
    {
        $modelMap = [
            'head_offices' => \App\Models\HeadOffice::class,
            'departments' => \App\Models\Department::class,
            'careers' => \App\Models\Career::class,
            'subsystems' => \App\Models\Subsystem::class,
            'document_types' => \App\Models\DocumentType::class,
            'academic_roles' => \App\Models\AcademicRole::class,
            'required_documents' => \App\Models\RequiredDocument::class,
            'metadata_schemas' => \App\Models\MetadataSchema::class,
            'processes' => \App\Models\Process::class,
            'process_categories' => \App\Models\ProcessCategory::class,
        ];

        return $modelMap[$tableName] ?? null;
    }

    /**
     * Obtener resumen del cambio
     */
    public function getChangeSummaryAttribute(): string
    {
        return $this->change_metadata['summary'] ?? "Acción {$this->action} en {$this->table_name}";
    }

    /**
     * Obtener cantidad de campos cambiados
     */
    public function getFieldsChangedCountAttribute(): int
    {
        return count($this->changed_fields ?? []);
    }

    /**
     * Verificar si es un cambio significativo
     */
    public function isSignificantChange(): bool
    {
        $insignificantFields = ['updated_at', 'created_at'];
        $significantFields = array_diff($this->changed_fields ?? [], $insignificantFields);

        return !empty($significantFields);
    }
}
