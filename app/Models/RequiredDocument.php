<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Required Documents
 *
 * Represents documents that are required for specific processes.
 * Can be role-specific and has ordering for display purposes.
 * Includes external references for microservice integration.
 *
 * @property string $id
 * @property string $process_id
 * @property string $document_type_id
 * @property string|null $academic_role_id
 * @property int $order
 * @property bool $mandatory
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property string|null $external_user_id
 * @property string|null $external_organization_id
 */
class RequiredDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'required_documents';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'process_id',
        'document_type_id',
        'academic_role_id',
        'metadata_schema_id', // 👈 Nuevo campo
        'order',
        'mandatory',
        'external_user_id',
        'external_organization_id'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'order' => 'integer',
        'mandatory' => 'boolean'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Get the process that requires this document.
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    /**
     * Get the document type for this required document.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the academic role for this required document (if role-specific).
     */
    public function academicRole(): BelongsTo
    {
        return $this->belongsTo(AcademicRole::class);
    }

    /**
     * Scope to filter by process.
     */
    public function scopeByProcess($query, string $processId)
    {
        return $query->where('process_id', $processId);
    }

    /**
     * Scope to filter by document type.
     */
    public function scopeByDocumentType($query, string $documentTypeId)
    {
        return $query->where('document_type_id', $documentTypeId);
    }

    /**
     * Scope to filter by academic role.
     */
    public function scopeByAcademicRole($query, ?string $academicRoleId = null)
    {
        return $query->where('academic_role_id', $academicRoleId);
    }

    /**
     * Scope to filter by external user.
     */
    public function scopeByExternalUser($query, string $externalUserId)
    {
        return $query->where('external_user_id', $externalUserId);
    }

    /**
     * Scope to filter by external organization.
     */
    public function scopeByExternalOrganization($query, string $externalOrganizationId)
    {
        return $query->where('external_organization_id', $externalOrganizationId);
    }

    /**
     * Scope to get only mandatory documents.
     */
    public function scopeMandatory($query)
    {
        return $query->where('mandatory', true);
    }

    /**
     * Scope to get only optional documents.
     */
    public function scopeOptional($query)
    {
        return $query->where('mandatory', false);
    }

    /**
     * Scope to get active required documents.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope to order by order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get documents for general use (not role-specific).
     */
    public function scopeGeneral($query)
    {
        return $query->whereNull('academic_role_id');
    }

    /**
     * Get role-specific documents.
     */
    public function scopeRoleSpecific($query)
    {
        return $query->whereNotNull('academic_role_id');
    }
}
