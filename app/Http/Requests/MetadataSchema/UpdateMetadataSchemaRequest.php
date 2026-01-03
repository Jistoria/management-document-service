<?php

namespace App\Http\Requests\MetadataSchema;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateMetadataSchemaRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schemaId = $this->route('metadata_schema');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('metadata_schemas', 'name')->ignore($schemaId)],
            'description' => ['sometimes', 'nullable', 'string'],
            'version' => ['sometimes', 'integer', 'min:1'],
            'fields' => ['sometimes', 'array'],
            'fields.*.metadataFieldId' => ['required_with:fields', 'uuid', 'exists:metadata_fields,id'],
            'fields.*.isRequired' => ['boolean'],
            'fields.*.isRepeatable' => ['boolean'],
            'fields.*.minOccurs' => ['nullable', 'integer', 'min:0'],
            'fields.*.maxOccurs' => ['nullable', 'integer', 'min:1'],
            'fields.*.allowDuplicates' => ['boolean'],
            'fields.*.sortOrder' => ['nullable', 'integer', 'min:1'],
            'fields.*.defaultValue' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'version' => 'versión',
            'fields' => 'campos del esquema',
            'fields.*.metadataFieldId' => 'campo de metadato',
            'fields.*.isRequired' => 'campo requerido',
            'fields.*.isRepeatable' => 'campo repetible',
            'fields.*.minOccurs' => 'mínimo de ocurrencias',
            'fields.*.maxOccurs' => 'máximo de ocurrencias',
            'fields.*.allowDuplicates' => 'permitir duplicados',
            'fields.*.sortOrder' => 'orden',
            'fields.*.defaultValue' => 'valor por defecto',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('fields', []) as $index => $field) {
                $minOccurs = $field['minOccurs'] ?? 0;
                $maxOccurs = $field['maxOccurs'] ?? null;
                $isRepeatable = $field['isRepeatable'] ?? false;

                if ($maxOccurs !== null && $minOccurs > $maxOccurs) {
                    $validator->errors()->add("fields.{$index}.maxOccurs", 'El máximo de ocurrencias debe ser mayor o igual al mínimo.');
                }

                if (!$isRepeatable && ($minOccurs > 1 || ($maxOccurs !== null && $maxOccurs > 1))) {
                    $validator->errors()->add("fields.{$index}.isRepeatable", 'El campo debe marcarse como repetible para permitir más de una ocurrencia.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->has('version')) {
            $data['version'] = (int) $this->version;
        }
        if ($this->has('fields')) {
            $fields = collect($this->input('fields', []))
                ->map(function ($field) {
                    if (array_key_exists('isRequired', $field)) {
                        $field['isRequired'] = filter_var($field['isRequired'], FILTER_VALIDATE_BOOLEAN);
                    }
                    if (array_key_exists('isRepeatable', $field)) {
                        $field['isRepeatable'] = filter_var($field['isRepeatable'], FILTER_VALIDATE_BOOLEAN);
                    }
                    if (array_key_exists('allowDuplicates', $field)) {
                        $field['allowDuplicates'] = filter_var($field['allowDuplicates'], FILTER_VALIDATE_BOOLEAN);
                    }
                    if (array_key_exists('minOccurs', $field) && $field['minOccurs'] !== null) {
                        $field['minOccurs'] = (int) $field['minOccurs'];
                    }
                    if (array_key_exists('maxOccurs', $field) && $field['maxOccurs'] !== null) {
                        $field['maxOccurs'] = (int) $field['maxOccurs'];
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
