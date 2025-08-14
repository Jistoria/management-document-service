<?php

namespace App\Http\Requests\RequiredDocument;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersRequiredDocumentRequest extends DefaultFiltersRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'processId' => ['nullable', 'uuid'],
            'documentTypeId' => ['nullable', 'uuid'],
            'academicRoleId' => ['nullable', 'uuid'],
            'metadataSchemaId' => ['nullable', 'uuid'],
            'mandatory' => ['nullable', 'boolean'],
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'processId' => 'proceso',
            'documentTypeId' => 'tipo de documento',
            'academicRoleId' => 'rol académico',
            'metadataSchemaId' => 'esquema de metadatos',
            'mandatory' => 'obligatorio',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'processId.uuid' => 'El campo proceso debe ser un UUID válido',
            'documentTypeId.uuid' => 'El campo tipo de documento debe ser un UUID válido',
            'academicRoleId.uuid' => 'El campo rol académico debe ser un UUID válido',
            'metadataSchemaId.uuid' => 'El campo esquema de metadatos debe ser un UUID válido',
            'mandatory.boolean' => 'El campo obligatorio debe ser verdadero o falso',
        ]);
    }
}
