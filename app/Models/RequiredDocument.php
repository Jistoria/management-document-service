<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCamelCaseAttributes;
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
 * @property string $name
 * @property string|null $description
 * @property int $order
 * @property string $code_default
 * @property bool $is_public
 * @property string|null $template_path
 * @property string|null $template_filename
 * @property string $created_by
 * @property string $updated_by
 * @property string deleted_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class RequiredDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable, HasCamelCaseAttributes;

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
        'name',
        'description',
        'code_default',
        'is_public',
        'metadata_schema_id',
        'order',
        'template_path',
        'template_filename',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'order' => 'integer'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Get the metadataSchema that this document.
     */
    public function metadataSchema(): BelongsTo
    {
        return $this->belongsTo(MetadataSchema::class, 'metadata_schema_id');
    }


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
