<?php

namespace App\Models;

use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use App\Traits\HasCamelCaseAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Constants\MetadataFieldDataType;

/**
 * Model for Metadata Fields
 *
 * Represents individual fields that can be composed within metadata schemas.
 * Supports validation and reference fields.
 *
 * @property string $id
 * @property string $field_key
 * @property string $label
 * @property int|null $entity_type_id
 * @property int|null $type_input_id
 * @property string $data_type
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class MetadataField extends Model
{
    use HasFactory, HasUuids, Auditable, HasCamelCaseAttributes, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'metadata_fields';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'field_key',
        'label',
        'entity_type_id',
        'type_input_id',
        'data_type',
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
        'entity_type_id' => 'integer',
        'type_input_id' => 'integer'
    ];

    /**
     * Available data types for metadata fields.
     */
    const DATA_TYPES = MetadataFieldDataType::ALL;

    /**
     * Get the metadata schema that owns this field.
     */
    public function metadataSchemas(): BelongsToMany
    {
        return $this->belongsToMany(MetadataSchema::class, 'metadata_schema_fields', 'metadata_field_id', 'metadata_schema_id')
            ->using(MetadataSchemaField::class)
            ->withPivot([
                'id',
                'is_required',
                'is_repeatable',
                'min_occurs',
                'max_occurs',
                'allow_duplicates',
                'sort_order',
                'default_value',
                'regex_pattern',
                'validation_error_message'
            ])
            ->withTimestamps();
    }

    /**
     * Scope to filter by data type.
     */
    public function scopeByDataType($query, string $dataType)
    {
        return $query->where('data_type', $dataType);
    }

    /**
     * Scope to filter by entity type.
     */
    public function scopeByEntityType($query, int $entityTypeId)
    {
        return $query->where('entity_type_id', $entityTypeId);
    }

    /**
     * Scope to filter by type input.
     */
    public function scopeByTypeInput($query, int $typeInputId)
    {
        return $query->where('type_input_id', $typeInputId);
    }

    /**
     * Validate value against the field's data type.
     */
    protected function validateDataType($value): bool
    {
        switch ($this->data_type) {
            case 'integer':
                return is_numeric($value) && is_int($value + 0);
            case 'decimal':
                return is_numeric($value);
            case 'boolean':
                return is_bool($value) || in_array($value, ['true', 'false', '1', '0', 1, 0]);
            case 'date':
                return strtotime($value) !== false;
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            case 'uuid':
                return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
            case 'json':
                json_decode($value);
                return json_last_error() === JSON_ERROR_NONE;
            default:
                return true; // string, text types
        }
    }

}
