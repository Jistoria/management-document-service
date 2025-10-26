<?php

namespace App\Http\Requests\StorageUnit;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreStorageUnitRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'storageUnitTypeId' => ['required', 'uuid', 'exists:storage_unit_types,id'],
            'parentId' => ['nullable', 'uuid', Rule::exists('storage_units', 'id')->whereNull('deleted_at')],
            'departmentId' => ['required', 'uuid', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            'label' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('storage_units', 'code')->whereNull('deleted_at')],
            'canHaveChildren' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'storageUnitTypeId.required' => 'El tipo de unidad es requerido',
            'storageUnitTypeId.uuid' => 'El ID del tipo debe ser un UUID válido',
            'storageUnitTypeId.exists' => 'El tipo de unidad seleccionado no existe',

            'parentId.uuid' => 'El ID de la unidad padre debe ser un UUID válido',
            'parentId.exists' => 'La unidad padre seleccionada no existe',

            'label.required' => 'La etiqueta es requerida',
            'label.string' => 'La etiqueta debe ser una cadena de texto',
            'label.max' => 'La etiqueta no puede exceder 255 caracteres',

            'code.string' => 'El código debe ser una cadena de texto',
            'code.max' => 'El código no puede exceder 50 caracteres',
            'code.regex' => 'El código solo puede contener letras mayúsculas, números, guiones y guiones bajos',
            'code.unique' => 'Ya existe una unidad con este código',

            'canHaveChildren' => 'El campo de capacidad para tener hijos debe ser verdadero o falso',
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

}
