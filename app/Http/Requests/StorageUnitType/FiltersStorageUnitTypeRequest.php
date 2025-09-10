<?php

namespace App\Http\Requests\StorageUnitType;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersStorageUnitTypeRequest extends DefaultFiltersRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'code' => ['nullable', 'string', 'max:50'],
            'level' => ['nullable', 'integer'],
            'created_by' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'code' => 'código',
            'level' => 'nivel',
            'created_by' => 'creado por',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.string' => 'El código debe ser una cadena de texto',
            'code.max' => 'El código no puede exceder 50 caracteres',
            'level.integer' => 'El nivel debe ser un número entero',
        ]);
    }
}
