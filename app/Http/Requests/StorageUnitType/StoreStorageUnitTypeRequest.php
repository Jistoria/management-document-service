<?php

namespace App\Http\Requests\StorageUnitType;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreStorageUnitTypeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('storage_unit_types', 'name')->whereNull('deleted_at'),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('storage_unit_types', 'code')->whereNull('deleted_at'),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'name.string' => 'El nombre debe ser una cadena de texto',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'name.unique' => 'Ya existe un tipo de unidad con este nombre',

            'code.required' => 'El código es requerido',
            'code.string' => 'El código debe ser una cadena de texto',
            'code.max' => 'El código no puede exceder 50 caracteres',
            'code.regex' => 'El código solo puede contener letras mayúsculas, números, guiones y guiones bajos',
            'code.unique' => 'Ya existe un tipo de unidad con este código',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'code' => 'código',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper(trim($this->code))]);
        }
    }
}
