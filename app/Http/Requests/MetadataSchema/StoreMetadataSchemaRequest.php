<?php

namespace App\Http\Requests\MetadataSchema;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreMetadataSchemaRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('metadata_schemas', 'name')],
            'description' => ['nullable', 'string'],
            'isCanonical' => ['boolean'],
            'version' => ['nullable', 'integer', 'min:1'],
            'fields' => ['array'],
            'fields.*.metadataFieldId' => ['required_with:fields', 'uuid', 'exists:metadata_fields,id'],
            'fields.*.isRequired' => ['boolean'],
            'fields.*.sortOrder' => ['nullable', 'integer', 'min:1'],
            'fields.*.defaultValue' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'isCanonical' => 'es canónico',
            'version' => 'versión',
            'fields' => 'campos del esquema',
            'fields.*.metadataFieldId' => 'campo de metadato',
            'fields.*.isRequired' => 'campo requerido',
            'fields.*.sortOrder' => 'orden',
            'fields.*.defaultValue' => 'valor por defecto',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->has('isCanonical')) {
            $data['isCanonical'] = $this->boolean('isCanonical');
        }
        if ($this->has('version')) {
            $data['version'] = (int) $this->version;
        }
        if ($this->has('fields')) {
            $fields = collect($this->input('fields', []))
                ->map(function ($field) {
                    if (array_key_exists('isRequired', $field)) {
                        $field['isRequired'] = filter_var($field['isRequired'], FILTER_VALIDATE_BOOLEAN);
                    }
                    return $field;
                })
                ->toArray();
            $data['fields'] = $fields;
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}

