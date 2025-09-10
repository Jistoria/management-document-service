<?php

namespace App\Http\Requests\StorageUnit;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersStorageUnitRequest extends DefaultFiltersRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'storageUnitTypeId' => ['nullable', 'uuid', 'exists:storage_unit_types,id'],
            'parentId' => ['nullable', 'uuid', 'exists:storage_units,id'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'storageUnitTypeId' => 'tipo de unidad',
            'parentId' => 'unidad padre',
            'code' => 'código',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'storageUnitTypeId.uuid' => 'El ID del tipo debe ser un UUID válido',
            'storageUnitTypeId.exists' => 'El tipo de unidad seleccionado no existe',
            'parentId.uuid' => 'El ID de la unidad padre debe ser un UUID válido',
            'parentId.exists' => 'La unidad padre seleccionada no existe',
            'code.string' => 'El código debe ser una cadena de texto',
            'code.max' => 'El código no puede exceder 50 caracteres',
        ]);
    }
}
