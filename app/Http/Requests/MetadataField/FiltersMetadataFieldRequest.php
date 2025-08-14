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
            'schema_id' => ['nullable', 'uuid'],
            'data_type' => ['nullable', 'string', Rule::in(MetadataFieldDataType::ALL)],
            'is_required' => ['nullable', 'boolean'],
            'is_reference' => ['nullable', 'boolean'],
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
