<?php

namespace App\Http\Requests\Process;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersProcessCategoryRequest extends DefaultFiltersRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'code' => ['nullable', 'string', 'max:255'],
            'createdBy' => ['nullable', 'string', 'max:255'],
            'subsystem_id' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'code' => 'código',
            'createdBy' => 'creado por',
            'subsystem_id' => 'subsistema',
        ]);
    }
}
