<?php

namespace App\Http\Requests\RequiredDocument;

use App\Http\Requests\BaseFormRequest;

class UpdateRequiredDocumentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'processId' => ['sometimes', 'nullable', 'uuid', 'exists:processes,id', 'required_without:metadataSchemaId'],
            'documentTypeId' => ['sometimes', 'uuid', 'exists:document_types,id'],
            'academicRoleId' => ['sometimes', 'nullable', 'uuid', 'exists:academic_roles,id'],
            'metadataSchemaId' => ['sometimes', 'nullable', 'uuid', 'exists:metadata_schemas,id', 'required_without:processId'],
            'codeDefault' => ['sometimes', 'nullable', 'string'],
            'urlResource' => ['sometimes', 'nullable', 'string'],
            'isPublic' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'templatePath' => ['sometimes', 'nullable', 'string', 'max:500'],
            'templateFilename' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'processId.uuid' => 'El campo proceso debe ser un UUID válido',
            'processId.exists' => 'El proceso seleccionado no existe',
            'processId.required_without' => 'El proceso es requerido cuando no se proporciona un esquema de metadatos',

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

            'templatePath.string' => 'La ruta de la plantilla debe ser una cadena de texto',
            'templatePath.max' => 'La ruta de la plantilla no puede exceder 500 caracteres',

            'templateFilename.string' => 'El nombre de archivo de la plantilla debe ser una cadena de texto',
            'templateFilename.max' => 'El nombre de archivo de la plantilla no puede exceder 255 caracteres',
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
            'templatePath' => 'ruta de la plantilla',
            'templateFilename' => 'nombre de archivo de la plantilla',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->has('isPublic')) {
            $data['isPublic'] = filter_var($this->isPublic, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        if ($this->has('order')) {
            $data['order'] = (int) $this->order;
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
