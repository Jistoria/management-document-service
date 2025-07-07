<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable()
    {
        // Crear registro de auditoría en INSERT
        static::created(function (Model $model) {
            $model->auditAction('INSERT', null, $model->getAttributes());
        });

        // Crear registro de auditoría en UPDATE
        static::updated(function (Model $model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();

            // Solo auditar si realmente hay cambios significativos
            if (!empty($changes) && !$model->onlyTimestampChanges($changes)) {
                $action = $model->determineUpdateAction($original, $model->getAttributes());
                $model->auditAction($action, $original, $model->getAttributes());
            }
        });

        // Crear registro de auditoría en DELETE
        static::deleted(function (Model $model) {
            $action = $model->isSoftDelete() ? 'SOFT_DELETE' : 'DELETE';
            $model->auditAction($action, $model->getAttributes(), null);
        });
    }

    /**
     * Crear un registro de auditoría
     */
    protected function auditAction(string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            $changedFields = $this->getChangedFields($oldValues, $newValues);
            $changeMetadata = $this->generateChangeMetadata($oldValues, $newValues, $action);
            $businessContext = $this->getBusinessContext();

            AuditLog::create([
                'table_name' => $this->getTable(),
                'record_id' => $this->getKey(),
                'action' => $action,

                // Información del usuario autenticado
                'user_id' => $this->getCurrentUserId(),
                'external_user_id' => $this->getCurrentExternalUserId(),
                'user_email' => $this->getCurrentUserEmail(),
                'user_name' => $this->getCurrentUserName(),

                // Información de la request
                'ip_address' => $this->getClientIp(),
                'user_agent' => Request::userAgent(),
                'endpoint' => Request::fullUrl(),
                'session_id' => session()->getId(),

                // Datos del cambio
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'changed_fields' => $changedFields,

                // Versionado
                'record_version_before' => $oldValues['version'] ?? null,
                'record_version_after' => $newValues['version'] ?? null,

                // Metadatos
                'change_metadata' => $changeMetadata,
                'business_context' => $businessContext,

                // Información de servicio
                'service_name' => config('app.name', 'management-document-service'),
                'service_version' => '1.0.0',
                'correlation_id' => $this->getCorrelationId(),
            ]);
        } catch (\Exception $e) {
            // Log del error pero no fallar la operación principal
            Log::error('Error creating audit log: ' . $e->getMessage(), [
                'table' => $this->getTable(),
                'record_id' => $this->getKey(),
                'action' => $action,
            ]);
        }
    }

    /**
     * Obtener el ID del usuario actual
     */
    protected function getCurrentUserId(): ?string
    {
        // Priorizar usuario autenticado
        if (Auth::check()) {
            return (string) Auth::user()->id;
        }

        // Usuario desde middleware (microservicio)
        $auditUser = request('audit_user');
        if ($auditUser) {
            return (string) $auditUser->id;
        }

        // Fallback a headers
        return Request::header('X-User-Id');
    }

    /**
     * Obtener el ID del usuario externo (desde JWT o API)
     */
    protected function getCurrentExternalUserId(): ?string
    {
        // Priorizar usuario autenticado con external_id
        if (Auth::check() && Auth::user() && method_exists(Auth::user(), 'external_id')) {
            return Auth::user()->external_id;
        }

        // Usuario desde middleware
        $auditUser = request('audit_user');
        if ($auditUser && $auditUser->external_id) {
            return $auditUser->external_id;
        }

        // Headers de microservicio
        return Request::header('X-User-Id') ?:
            Request::header('X-External-User-Id') ?:
            $this->getCurrentUserId();
    }

    /**
     * Obtener email del usuario actual
     */
    protected function getCurrentUserEmail(): ?string
    {
        // Priorizar usuario autenticado
        if (Auth::check() && Auth::user() && isset(Auth::user()->email)) {
            return Auth::user()->email;
        }

        // Usuario desde middleware
        $auditUser = request('audit_user');
        if ($auditUser && $auditUser->email) {
            return $auditUser->email;
        }

        // Headers
        return Request::header('X-User-Email');
    }

    /**
     * Obtener nombre del usuario actual
     */
    protected function getCurrentUserName(): ?string
    {
        // Priorizar usuario autenticado
        if (Auth::check() && Auth::user() && isset(Auth::user()->name)) {
            return Auth::user()->name;
        }

        // Usuario desde middleware
        $auditUser = request('audit_user');
        if ($auditUser && $auditUser->name) {
            return $auditUser->name;
        }

        // Headers
        return Request::header('X-User-Name');
    }

    /**
     * Obtener IP del cliente
     */
    protected function getClientIp(): ?string
    {
        return Request::ip();
    }

    /**
     * Obtener correlation ID para trazabilidad distribuida
     */
    protected function getCorrelationId(): ?string
    {
        return Request::header('X-Correlation-ID') ?:
            Request::header('X-Request-ID') ?:
            session()->getId();
    }

    /**
     * Determinar el tipo de acción en UPDATE
     */
    protected function determineUpdateAction(array $original, array $current): string
    {
        // Detectar RESTORE (deleted_at cambia de valor a null)
        if (
            isset($original['deleted_at']) && $original['deleted_at'] !== null &&
            isset($current['deleted_at']) && $current['deleted_at'] === null
        ) {
            return 'RESTORE';
        }

        // Detectar SOFT_DELETE (deleted_at cambia de null a valor)
        if ((!isset($original['deleted_at']) || $original['deleted_at'] === null) &&
            isset($current['deleted_at']) && $current['deleted_at'] !== null
        ) {
            return 'SOFT_DELETE';
        }

        return 'UPDATE';
    }

    /**
     * Verificar si es soft delete
     */
    protected function isSoftDelete(): bool
    {
        return method_exists($this, 'getDeletedAtColumn') &&
            $this->{$this->getDeletedAtColumn()} !== null;
    }

    /**
     * Verificar si solo cambiaron timestamps
     */
    protected function onlyTimestampChanges(array $changes): bool
    {
        $timestampFields = ['created_at', 'updated_at'];
        $nonTimestampChanges = array_diff(array_keys($changes), $timestampFields);
        return empty($nonTimestampChanges);
    }

    /**
     * Obtener campos que cambiaron
     */
    protected function getChangedFields(?array $oldValues, ?array $newValues): array
    {
        if ($oldValues === null) {
            return $newValues ? array_keys($newValues) : [];
        }

        if ($newValues === null) {
            return [];
        }

        $changed = [];
        foreach (array_merge(array_keys($oldValues), array_keys($newValues)) as $key) {
            $oldVal = $oldValues[$key] ?? null;
            $newVal = $newValues[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changed[] = $key;
            }
        }

        return array_unique($changed);
    }

    /**
     * Generar metadatos detallados del cambio
     */
    protected function generateChangeMetadata(?array $oldValues, ?array $newValues, string $action): array
    {
        if ($oldValues === null) {
            return [
                'change_type' => 'create',
                'fields_count' => $newValues ? count($newValues) : 0,
                'summary' => 'Nuevo registro creado',
            ];
        }

        if ($newValues === null) {
            return [
                'change_type' => 'delete',
                'fields_count' => count($oldValues),
                'summary' => 'Registro eliminado',
            ];
        }

        $fieldChanges = [];
        $changedCount = 0;

        foreach (array_merge(array_keys($oldValues), array_keys($newValues)) as $key) {
            $oldVal = $oldValues[$key] ?? null;
            $newVal = $newValues[$key] ?? null;

            if ($oldVal !== $newVal) {
                $fieldChanges[$key] = [
                    'from' => $oldVal,
                    'to' => $newVal,
                    'changed' => true,
                ];
                $changedCount++;
            }
        }

        return [
            'change_type' => 'update',
            'fields_changed_count' => $changedCount,
            'field_details' => $fieldChanges,
            'summary' => "Actualización en tabla {$this->getTable()} con {$changedCount} campos modificados",
        ];
    }

    /**
     * Obtener contexto de negocio
     */
    protected function getBusinessContext(): array
    {
        $tableContextMap = [
            'required_documents' => 'document_management',
            'metadata_schemas' => 'metadata_system',
            'processes' => 'process_management',
            'head_offices' => 'organizational_structure',
            'departments' => 'organizational_structure',
            'careers' => 'academic_structure',
        ];

        return [
            'table_context' => $tableContextMap[$this->getTable()] ?? 'general',
            'model_class' => get_class($this),
            'environment' => app()->environment(),
            'route_name' => Request::route()?->getName(),
            'request_method' => Request::method(),
        ];
    }

    /**
     * Método para auditoría manual con razón específica
     */
    public function auditManual(string $action, string $reason, array $metadata = []): void
    {
        $oldValues = $this->getOriginal();
        $newValues = $this->getAttributes();

        $this->auditAction($action, $oldValues, $newValues);

        // Actualizar con razón específica
        $lastAudit = AuditLog::where('table_name', $this->getTable())
            ->where('record_id', $this->getKey())
            ->latest()
            ->first();

        if ($lastAudit) {
            $lastAudit->update([
                'change_reason' => $reason,
                'change_metadata' => array_merge($lastAudit->change_metadata ?? [], $metadata),
            ]);
        }
    }
}
