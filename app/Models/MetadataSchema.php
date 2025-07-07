<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Metadata Schemas
 *
 * Represents schemas for dynamic metadata fields.
 * Supports versioning, inheritance, and external system integration.
 *
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property string|null $parent_schema_id
 * @property bool $is_canonical
 * @property int $version
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property string|null $external_system_id
 * @property string|null $api_endpoint
 * @property int $cache_ttl
 * @property string|null $created_by
 * @property string|null $updated_by
 */
class MetadataSchema extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasAuditFields, Auditable {
        Auditable::getCurrentExternalUserId insteadof HasAuditFields;
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'metadata_schemas';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'parent_schema_id',
        'is_canonical',
        'version',
        'external_system_id',
        'api_endpoint',
        'cache_ttl',
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
        'is_canonical' => 'boolean',
        'version' => 'integer',
        'cache_ttl' => 'integer'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Get the parent schema (for inheritance).
     */
    public function parentSchema(): BelongsTo
    {
        return $this->belongsTo(MetadataSchema::class, 'parent_schema_id');
    }

    /**
     * Get the child schemas that inherit from this schema.
     */
    public function childSchemas(): HasMany
    {
        return $this->hasMany(MetadataSchema::class, 'parent_schema_id');
    }

    /**
     * Get the metadata fields for this schema.
     */
    public function metadataFields(): HasMany
    {
        return $this->hasMany(MetadataField::class, 'schema_id');
    }

    /**
     * Get active metadata fields for this schema ordered by field_order.
     */
    public function activeMetadataFields(): HasMany
    {
        return $this->metadataFields()->orderBy('field_order');
    }

    /**
     * Get the schema events for this schema.
     */
    public function schemaEvents(): HasMany
    {
        return $this->hasMany(MetadataSchemaEvent::class, 'schema_id');
    }

    /**
     * Scope to filter by name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Scope to get canonical schemas.
     */
    public function scopeCanonical($query)
    {
        return $query->where('is_canonical', true);
    }

    /**
     * Scope to get schemas for a specific external system.
     */
    public function scopeByExternalSystem($query, string $externalSystemId)
    {
        return $query->where('external_system_id', $externalSystemId);
    }

    /**
     * Scope to get active schemas.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get all inherited fields from parent schemas.
     */
    public function getInheritedFields()
    {
        $fields = collect();

        if ($this->parentSchema) {
            $fields = $fields->merge($this->parentSchema->getInheritedFields());
            $fields = $fields->merge($this->parentSchema->activeMetadataFields);
        }

        return $fields;
    }

    /**
     * Get all fields including inherited ones.
     */
    public function getAllFields()
    {
        return $this->getInheritedFields()
            ->merge($this->activeMetadataFields)
            ->sortBy('field_order');
    }

    /**
     * Check if schema has external API integration.
     */
    public function hasApiIntegration(): bool
    {
        return !empty($this->api_endpoint);
    }

    /**
     * Get cache TTL in seconds.
     */
    public function getCacheTtl(): int
    {
        return $this->cache_ttl ?? 3600; // Default 1 hour
    }
}
