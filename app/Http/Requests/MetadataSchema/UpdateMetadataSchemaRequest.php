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
            'parentSchemaId' => ['sometimes', 'nullable', 'uuid', 'exists:metadata_schemas,id'],
            'isCanonical' => ['sometimes', 'boolean'],
            'version' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'parentSchemaId' => 'esquema padre',
            'isCanonical' => 'es canónico',
            'version' => 'versión',
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
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}

