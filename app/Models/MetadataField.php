<?php

namespace App\Models;

use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use App\Traits\HasCamelCaseAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Constants\MetadataFieldDataType;

/**
 * Model for Metadata Fields
 *
 * Represents individual fields within a metadata schema.
 * Supports validation, OCR hints, and reference fields.
 *
 * @property string $id
 * @property string $schema_id
 * @property string $name
 * @property string $data_type
 * @property bool $is_required
 * @property string|null $default_value
 * @property string|null $validation_regex
 * @property int|null $field_order
 * @property array|null $lookup_keywords
 * @property string|null $ocr_hint
 * @property bool $ignore_in_similarity
 * @property bool $is_reference
 * @property string|null $reference_entity
 * @property string $reference_column
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MetadataField extends Model
{
    use HasFactory, HasUuids, Auditable, HasCamelCaseAttributes;

    /**
     * The table associated with the model.
     */
    protected $table = 'metadata_fields';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'schema_id',
        'name',
        'data_type',
        'is_required',
        'default_value',
        'validation_regex',
        'field_order',
        'lookup_keywords',
        'ocr_hint',
        'ignore_in_similarity',
        'is_reference',
        'reference_entity',
        'reference_column'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_required' => 'boolean',
        'field_order' => 'integer',
        'lookup_keywords' => 'array',
        'ignore_in_similarity' => 'boolean',
        'is_reference' => 'boolean'
    ];

    /**
     * Available data types for metadata fields.
     */
    const DATA_TYPES = MetadataFieldDataType::ALL;

    /**
     * Get the metadata schema that owns this field.
     */
    public function metadataSchema(): BelongsTo
    {
        return $this->belongsTo(MetadataSchema::class, 'schema_id');
    }

    /**
     * Scope to filter by schema.
     */
    public function scopeBySchema($query, string $schemaId)
    {
        return $query->where('schema_id', $schemaId);
    }

    /**
     * Scope to filter by data type.
     */
    public function scopeByDataType($query, string $dataType)
    {
        return $query->where('data_type', $dataType);
    }

    /**
     * Scope to get required fields.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope to get optional fields.
     */
    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    /**
     * Scope to get reference fields.
     */
    public function scopeReference($query)
    {
        return $query->where('is_reference', true);
    }

    /**
     * Scope to get non-reference fields.
     */
    public function scopeNonReference($query)
    {
        return $query->where('is_reference', false);
    }

    /**
     * Scope to order by field order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('field_order');
    }

    /**
     * Validate a value against this field's constraints.
     */
    public function validateValue($value): bool
    {
        // Check if required field has value
        if ($this->is_required && empty($value)) {
            return false;
        }

        // Skip validation if value is empty and field is optional
        if (empty($value) && !$this->is_required) {
            return true;
        }

        // Validate against regex if provided
        if ($this->validation_regex && !preg_match($this->validation_regex, $value)) {
            return false;
        }

        // Validate data type
        return $this->validateDataType($value);
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

    /**
     * Get the formatted default value based on data type.
     */
    public function getFormattedDefaultValue()
    {
        if (empty($this->default_value)) {
            return null;
        }

        switch ($this->data_type) {
            case 'integer':
                return (int) $this->default_value;
            case 'decimal':
                return (float) $this->default_value;
            case 'boolean':
                return in_array(strtolower($this->default_value), ['true', '1', 'yes']);
            case 'json':
                return json_decode($this->default_value, true);
            default:
                return $this->default_value;
        }
    }

    /**
     * Check if this field has OCR capabilities.
     */
    public function hasOcrHint(): bool
    {
        return !empty($this->ocr_hint);
    }

    /**
     * Check if this field should be included in similarity calculations.
     */
    public function isIncludedInSimilarity(): bool
    {
        return !$this->ignore_in_similarity;
    }
}
