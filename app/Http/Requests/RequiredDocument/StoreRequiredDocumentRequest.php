<?php

namespace App\Http\Requests\RequiredDocument;

use App\Http\Requests\BaseFormRequest;

class StoreRequiredDocumentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'processId' => ['nullable', 'uuid', 'exists:processes,id', 'required_without:metadataSchemaId'],
            'documentTypeId' => ['required', 'uuid', 'exists:document_types,id'],
            'academicRoleId' => ['nullable', 'uuid', 'exists:academic_roles,id'],
            'metadataSchemaId' => ['nullable', 'uuid', 'exists:metadata_schemas,id', 'required_without:processId'],
            'codeDefault' => ['nullable', 'string'],
            'urlResource' => ['nullable', 'string'],
            'isPublic' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
            'mandatory' => ['nullable', 'boolean'],
            'externalUserId' => ['nullable', 'string', 'max:255'],
            'externalOrganizationId' => ['nullable', 'string', 'max:255'],
            'generateDefaultCode' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'processId.uuid' => 'El campo proceso debe ser un UUID válido',
            'processId.exists' => 'El proceso seleccionado no existe',
            'processId.required_without' => 'El proceso es requerido cuando no se proporciona un esquema de metadatos',

            'documentTypeId.required' => 'El tipo de documento es requerido',
            'documentTypeId.uuid' => 'El campo tipo de documento debe ser un UUID válido',
            'documentTypeId.exists' => 'El tipo de documento seleccionado no existe',

            'academicRoleId.uuid' => 'El campo rol académico debe ser un UUID válido',
            'academicRoleId.exists' => 'El rol académico seleccionado no existe',

            'metadataSchemaId.uuid' => 'El campo esquema de metadatos debe ser un UUID válido',
            'metadataSchemaId.exists' => 'El esquema de metadatos seleccionado no existe',
            'metadataSchemaId.required_without' => 'El esquema de metadatos es requerido cuando no se proporciona un proceso',

            'codeDefault.string' => 'El código predeterminado debe ser una cadena de texto',

            'urlResource.string' => 'La URL del recurso debe ser una cadena de texto',

            'isPublic.boolean' => 'El campo de visibilidad pública debe ser verdadero o falso',

            'order.integer' => 'El orden debe ser un número entero',
            'order.min' => 'El orden no puede ser negativo',

            'mandatory.boolean' => 'El campo obligatorio debe ser verdadero o falso',

            'externalUserId.string' => 'El ID de usuario externo debe ser una cadena de texto',
            'externalUserId.max' => 'El ID de usuario externo no puede exceder 255 caracteres',

            'externalOrganizationId.string' => 'El ID de organización externa debe ser una cadena de texto',
            'externalOrganizationId.max' => 'El ID de organización externa no puede exceder 255 caracteres',
        ];
    }

    public function attributes(): array
    {
        return [
            'processId' => 'proceso',
            'documentTypeId' => 'tipo de documento',
            'academicRoleId' => 'rol académico',
            'metadataSchemaId' => 'esquema de metadatos',
            'codeDefault' => 'código predeterminado',
            'urlResource' => 'URL del recurso',
            'isPublic' => 'visibilidad pública',
            'order' => 'orden',
            'mandatory' => 'obligatorio',
            'externalUserId' => 'ID de usuario externo',
            'externalOrganizationId' => 'ID de organización externa',
            'generateDefaultCode' => 'generar código por defecto'
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->has('mandatory')) {
            $data['mandatory'] = filter_var($this->mandatory, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        if ($this->has('isPublic')) {
            $data['isPublic'] = filter_var($this->isPublic, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        if ($this->has('generateDefaultCode')) {
            $data['generateDefaultCode'] = filter_var($this->generateDefaultCode, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        if ($this->has('order')) {
            $data['order'] = (int) $this->order;
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
