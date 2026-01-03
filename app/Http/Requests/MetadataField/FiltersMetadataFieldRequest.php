<?php

namespace App\Http\Requests\MetadataField;

use App\Constants\MetadataFieldDataType;
use App\Http\Requests\Globals\DefaultFiltersRequest;
use Illuminate\Validation\Rule;

/**
 * Request object for filtering metadata fields.
 *
 * Extends the default filter request allowing clients to
 * filter by schema, data type and reference flags.
 */
class FiltersMetadataFieldRequest extends DefaultFiltersRequest
{
    /**
     * Validation rules for metadata field filters.
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'field_key' => ['nullable', 'string'],
            'data_type' => ['nullable', 'string', Rule::in(MetadataFieldDataType::ALL)],
            'type_input_id' => ['nullable', 'string'],
            'entity_type_id' => ['nullable', 'uuid'],
            'is_reference' => ['nullable', 'boolean'],
            'schema_id' => ['nullable', 'uuid'],
            'without_schema_id' => ['nullable', 'uuid'],
            'withoutSchemaId' => ['nullable', 'uuid'],
        ]);
    }

    /**
     * Normalize the data type before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('data_type')) {
            $this->merge(['data_type' => MetadataFieldDataType::normalize($this->data_type)]);
        }
    }
}
