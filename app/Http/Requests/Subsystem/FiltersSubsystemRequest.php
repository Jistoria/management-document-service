<?php

namespace App\Http\Requests\Subsystem;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersSubsystemRequest extends DefaultFiltersRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'code' => ['nullable', 'string', 'max:255'],
            'createdBy' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'code' => 'código',
            'createdBy' => 'creado por',
        ]);
    }
}
