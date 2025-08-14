<?php

namespace App\Http\Requests\MetadataField;

use App\Constants\MetadataFieldDataType;
use App\Helpers\MetadataFieldEntityMap;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for updating existing metadata fields.
 */
class UpdateMetadataFieldRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating a metadata field.
     */
    public function rules(): array
    {
        return [
            'schema_id' => ['sometimes', 'uuid', 'exists:metadata_schemas,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'data_type' => ['sometimes', 'string', Rule::in(MetadataFieldDataType::ALL)],
            'is_required' => ['sometimes', 'boolean'],
            'default_value' => ['nullable', 'string'],
            'validation_regex' => ['nullable', 'string'],
            'field_order' => ['nullable', 'integer', 'min:1'],
            'lookup_keywords' => ['nullable', 'array'],
            'lookup_keywords.*' => ['string'],
            'ocr_hint' => ['nullable', 'string'],
            'ignore_in_similarity' => ['sometimes', 'boolean'],
            'is_reference' => ['sometimes', 'boolean'],
            'reference_entity' => ['nullable', 'string', Rule::in(MetadataFieldEntityMap::keys())],
            'reference_column' => ['nullable', 'string'],
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
