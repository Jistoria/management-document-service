<?php

namespace App\Http\Requests\DocumentType;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersDocumentTypeRequest extends DefaultFiltersRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            // Reglas específicas para DocumentType
            'code' => [
                'nullable',
                'string',
                'max:255',
            ],
            'created_by' => [
                'nullable',
                'string',
                'max:255',
            ],
            'has_required_documents' => [
                'nullable',
                'boolean',
            ],
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'code' => 'código',
            'created_by' => 'creado por',
            'has_required_documents' => 'tiene documentos requeridos',
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.string' => 'El código debe ser una cadena de texto',
            'code.max' => 'El código no puede exceder 255 caracteres',
            'created_by.string' => 'El campo creado por debe ser una cadena de texto',
            'created_by.max' => 'El campo creado por no puede exceder 255 caracteres',
            'has_required_documents.boolean' => 'El campo tiene documentos requeridos debe ser verdadero o falso',
        ]);
    }
}
