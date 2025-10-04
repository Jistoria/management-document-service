<?php

namespace App\Http\Requests\Process;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersProcessRequest extends DefaultFiltersRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'processCategoryId' => ['nullable', 'string', 'uuid', 'exists:process_categories,id'],
            'parentId' => ['nullable', 'string', 'uuid', 'exists:processes,id'],
            'code' => ['nullable', 'string', 'max:255'],
            'createdBy' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'processCategoryId' => 'categoría de proceso',
            'parentId' => 'proceso padre',
            'code' => 'código',
            'createdBy' => 'creado por',
        ]);
    }
}
