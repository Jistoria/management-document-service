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
            'entityTypeId' => ['sometimes', 'nullable', 'integer'],
            'typeInputId' => ['sometimes', 'nullable', 'integer'],
            'dataType' => ['sometimes', 'string', Rule::in(MetadataFieldDataType::ALL)],
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
        if ($this->has('entityTypeId')) {
            $data['entityTypeId'] = (int) $this->entityTypeId;
        }
        if ($this->has('typeInputId')) {
            $data['typeInputId'] = (int) $this->typeInputId;
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
