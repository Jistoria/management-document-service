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
            'fieldKey' => ['sometimes', 'string', 'max:255', Rule::unique('metadata_fields', 'field_key')->ignore($this->route('metadata_field'))],
            'label' => ['sometimes', 'string', 'max:255'],
            'entityTypeId' => ['sometimes', 'nullable', 'uuid'],
            'typeInputId' => ['sometimes', 'string', 'max:255'],
            'dataType' => ['sometimes', 'string', Rule::in(MetadataFieldDataType::ALL)],
            'isReference' => ['sometimes', 'boolean'],
            'referenceEntity' => ['sometimes', 'nullable', 'string', 'max:255'],
            'referenceColumn' => ['sometimes', 'nullable', 'string', 'max:255'],
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
        if ($this->has('isReference')) {
            $data['isReference'] = $this->boolean('isReference');
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
