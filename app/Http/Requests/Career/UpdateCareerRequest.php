<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCareerRequest extends FormRequest
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
        $careerId = $this->route('career');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('careers', 'code')->ignore($careerId),
            ],
            'codeNumeric' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('careers', 'code_numeric')->ignore($careerId),
            ],
            'departmentId' => [
                'sometimes',
                'required',
                'uuid',
                'exists:departments,id',
            ],
            'updatedBy' => [
                'nullable',
                'string',
                'max:255',
            ],
            'version' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la carrera es requerido.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',

            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede tener más de 20 caracteres.',
            'code.regex' => 'El código solo puede contener letras mayúsculas, números, guiones y guiones bajos.',
            'code.unique' => 'Este código ya está en uso por otra carrera.',
            'codeNumeric.string' => 'El código numérico debe ser una cadena de texto.',
            'codeNumeric.max' => 'El código numérico no puede tener más de 50 caracteres.',
            'codeNumeric.unique' => 'Este código numérico ya está en uso por otra carrera.',

            'departmentId.required' => 'El departamento es requerido.',
            'departmentId.uuid' => 'El ID del departamento debe ser un UUID válido.',
            'departmentId.exists' => 'El departamento seleccionado no existe.',

            'updatedBy.string' => 'El campo actualizado por debe ser una cadena de texto.',
            'updatedBy.max' => 'El campo actualizado por no puede tener más de 255 caracteres.',

            'version.integer' => 'La versión debe ser un número entero.',
            'version.min' => 'La versión debe ser mayor o igual a 0.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'code' => 'código',
            'codeNumeric' => 'código numérico',
            'departmentId' => 'departamento',
            'updatedBy' => 'actualizado por',
            'version' => 'versión',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Handle camelCase to snake_case conversion for compatibility
        if ($this->has('departmentId') && !$this->has('department_id')) {
            $this->merge(['department_id' => $this->input('departmentId')]);
        }

        if ($this->has('updatedBy') && !$this->has('updated_by')) {
            $this->merge(['updated_by' => $this->input('updatedBy')]);
        }

        // Normalize code to uppercase if provided
        if ($this->has('code') && !empty($this->input('code'))) {
            $this->merge([
                'code' => strtoupper(trim($this->input('code')))
            ]);
        }

        if ($this->has('codeNumeric')) {
            $this->merge([
                'codeNumeric' => trim((string) $this->input('codeNumeric'))
            ]);
        }

        // Trim name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->input('name'))
            ]);
        }

        // Set default updated_by if not provided
        if (!$this->has('updatedBy') && !$this->has('updated_by')) {
            $this->merge([
                'updated_by' => 'system'
            ]);
        }
    }
}
