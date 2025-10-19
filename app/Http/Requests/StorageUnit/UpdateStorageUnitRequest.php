<?php

namespace App\Http\Requests\StorageUnit;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateStorageUnitRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unitId = $this->route('storage_unit');

        return [
            'storageUnitTypeId' => ['sometimes', 'uuid', 'exists:storage_unit_types,id'],
            'parentId' => ['sometimes', 'nullable', 'uuid', 'exists:storage_units,id'],
            'label' => ['sometimes', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('storage_units', 'code')->ignore($unitId)->whereNull('deleted_at'),
            ],
            'canHaveChildren' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'storageUnitTypeId.uuid' => 'El ID del tipo debe ser un UUID válido',
            'storageUnitTypeId.exists' => 'El tipo de unidad seleccionado no existe',

            'parentId.uuid' => 'El ID de la unidad padre debe ser un UUID válido',
            'parentId.exists' => 'La unidad padre seleccionada no existe',

            'label.string' => 'La etiqueta debe ser una cadena de texto',
            'label.max' => 'La etiqueta no puede exceder 255 caracteres',

            'code.string' => 'El código debe ser una cadena de texto',
            'code.max' => 'El código no puede exceder 50 caracteres',
            'code.regex' => 'El código solo puede contener letras mayúsculas, números, guiones y guiones bajos',
            'code.unique' => 'Ya existe una unidad con este código',

            'canHaveChildren.boolean' => 'El campo de capacidad para tener hijos debe ser verdadero o falso',
        ];
    }

    public function attributes(): array
    {
        return [
            'storageUnitTypeId' => 'tipo de unidad',
            'parentId' => 'unidad padre',
            'label' => 'etiqueta',
            'code' => 'código',
            'canHaveChildren' => 'capacidad para tener hijos',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('storageUnitTypeId') && !$this->has('storage_unit_type_id')) {
            $this->merge(['storage_unit_type_id' => $this->input('storageUnitTypeId')]);
        }
        if ($this->has('parentId') && !$this->has('parent_id')) {
            $this->merge(['parent_id' => $this->input('parentId')]);
        }
        if ($this->has('code') && $this->code !== null) {
            $this->merge(['code' => strtoupper(trim($this->code))]);
        }
    }
}
