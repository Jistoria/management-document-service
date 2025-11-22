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
            'is_canonical' => ['nullable', 'boolean'],
            'external_system_id' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'is_canonical' => 'es canónico',
            'external_system_id' => 'sistema externo',
        ]);
    }
}

