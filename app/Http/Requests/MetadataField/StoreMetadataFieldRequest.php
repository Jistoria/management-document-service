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
            'schemaId' => ['required', 'uuid', 'exists:metadata_schemas,id'],
            'name' => ['required', 'string', 'max:255'],
            'dataType' => ['required', 'string', Rule::in(MetadataFieldDataType::ALL)],
            'isRequired' => ['boolean'],
            'defaultValue' => ['nullable', 'string'],
            'validationRegex' => ['nullable', 'string'],
            'fieldOrder' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'schemaId' => 'esquema',
            'name' => 'nombre',
            'dataType' => 'tipo de dato',
            'isRequired' => 'es requerido',
            'defaultValue' => 'valor por defecto',
            'validationRegex' => 'expresión regular',
            'fieldOrder' => 'orden',
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

        if ($this->boolean('isRequired')) {
            $data['isRequired'] = $this->boolean('isRequired');
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
