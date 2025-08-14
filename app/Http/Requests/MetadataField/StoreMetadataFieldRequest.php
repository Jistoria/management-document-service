<?php

namespace App\Http\Requests\MetadataField;

use App\Constants\MetadataFieldDataType;
use App\Helpers\MetadataFieldEntityMap;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for creating metadata fields.
 *
 * Applies validation rules and normalizes input so fields are
 * consistent across services.
 */
class StoreMetadataFieldRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for storing a metadata field.
     */
    public function rules(): array
    {
        return [
            'schema_id' => ['required', 'uuid', 'exists:metadata_schemas,id'],
            'name' => ['required', 'string', 'max:255'],
            'data_type' => ['required', 'string', Rule::in(MetadataFieldDataType::ALL)],
            'is_required' => ['boolean'],
            'default_value' => ['nullable', 'string'],
            'validation_regex' => ['nullable', 'string'],
            'field_order' => ['nullable', 'integer', 'min:1'],
            'lookup_keywords' => ['nullable', 'array'],
            'lookup_keywords.*' => ['string'],
            'ocr_hint' => ['nullable', 'string'],
            'ignore_in_similarity' => ['boolean'],
            'is_reference' => ['boolean'],
            'reference_entity' => ['nullable', 'string', Rule::in(MetadataFieldEntityMap::keys())],
            'reference_column' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'schema_id' => 'esquema',
            'name' => 'nombre',
            'data_type' => 'tipo de dato',
            'is_required' => 'es requerido',
            'default_value' => 'valor por defecto',
            'validation_regex' => 'expresión regular',
            'field_order' => 'orden',
            'lookup_keywords' => 'palabras clave',
            'ocr_hint' => 'pista OCR',
            'ignore_in_similarity' => 'ignorar en similitud',
            'is_reference' => 'es referencia',
            'reference_entity' => 'entidad de referencia',
            'reference_column' => 'columna de referencia',
        ];
    }

    /**
     * Normalize fields prior to validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('data_type')) {
            $data['data_type'] = MetadataFieldDataType::normalize($this->data_type);
        }

        if ($this->boolean('is_required')) {
            $data['is_required'] = $this->boolean('is_required');
        }
        if ($this->boolean('ignore_in_similarity')) {
            $data['ignore_in_similarity'] = $this->boolean('ignore_in_similarity');
        }
        if ($this->boolean('is_reference')) {
            $data['is_reference'] = $this->boolean('is_reference');
        }

        if ($this->has('reference_entity')) {
            $entity = MetadataFieldEntityMap::isValidKey($this->reference_entity)
                ? $this->reference_entity
                : null;
            if ($entity && !$this->has('reference_column')) {
                $data['reference_column'] = MetadataFieldEntityMap::getColumn($entity);
            }
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
