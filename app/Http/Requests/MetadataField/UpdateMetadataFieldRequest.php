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
            'schemaId' => ['sometimes', 'uuid', 'exists:metadata_schemas,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'dataType' => ['sometimes', 'string', Rule::in(MetadataFieldDataType::ALL)],
            'isRequired' => ['sometimes', 'boolean'],
            'defaultValue' => ['nullable', 'string'],
            'validationRegex' => ['nullable', 'string'],
            'fieldOrder' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Normalize fields prior to validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->has('dataType')) {
            $data['dataType'] = MetadataFieldDataType::normalize($this->dataType);
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
