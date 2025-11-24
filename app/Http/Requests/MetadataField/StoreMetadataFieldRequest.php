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
            'fieldKey' => ['required', 'string', 'max:255', Rule::unique('metadata_fields', 'field_key')],
            'label' => ['required', 'string', 'max:255'],
            'entityTypeId' => ['nullable', 'integer'],
            'typeInputId' => ['nullable', 'integer'],
            'dataType' => ['required', 'string', Rule::in(MetadataFieldDataType::ALL)],
        ];
    }

    public function attributes(): array
    {
        return [
            'fieldKey' => 'clave del campo',
            'label' => 'etiqueta',
            'entityTypeId' => 'tipo de entidad',
            'typeInputId' => 'tipo de entrada',
            'dataType' => 'tipo de dato',
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
