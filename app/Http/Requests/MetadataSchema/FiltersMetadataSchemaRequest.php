<?php

namespace App\Http\Requests\MetadataSchema;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersMetadataSchemaRequest extends DefaultFiltersRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'parent_schema_id' => ['nullable', 'uuid', 'exists:metadata_schemas,id'],
            'is_canonical' => ['nullable', 'boolean'],
            'external_system_id' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'parent_schema_id' => 'esquema padre',
            'is_canonical' => 'es canónico',
            'external_system_id' => 'sistema externo',
        ]);
    }
}

