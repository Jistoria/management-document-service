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
            'parent_schema_id' => ['sometimes', 'nullable', 'uuid', 'exists:metadata_schemas,id'],
            'is_canonical' => ['sometimes', 'boolean'],
            'version' => ['sometimes', 'integer', 'min:1'],
            'external_system_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'api_endpoint' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cache_ttl' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'parent_schema_id' => 'esquema padre',
            'is_canonical' => 'es canónico',
            'version' => 'versión',
            'external_system_id' => 'sistema externo',
            'api_endpoint' => 'endpoint API',
            'cache_ttl' => 'TTL de caché',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->has('is_canonical')) {
            $data['is_canonical'] = $this->boolean('is_canonical');
        }
        if ($this->has('version')) {
            $data['version'] = (int) $this->version;
        }
        if ($this->has('cache_ttl')) {
            $data['cache_ttl'] = (int) $this->cache_ttl;
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}

