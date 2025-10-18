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
            'parentSchemaId' => ['nullable', 'uuid', 'exists:metadata_schemas,id'],
            'isCanonical' => ['boolean'],
            'version' => ['nullable', 'integer', 'min:1']
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

