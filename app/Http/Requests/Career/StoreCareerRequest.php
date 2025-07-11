<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCareerRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'code' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                'unique:careers,code',
            ],
            'departmentId' => [
                'required',
                'uuid',
                'exists:departments,id',
            ],
            'createdBy' => [
                'nullable',
                'string',
                'max:255',
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

            'departmentId.required' => 'El departamento es requerido.',
            'departmentId.uuid' => 'El ID del departamento debe ser un UUID válido.',
            'departmentId.exists' => 'El departamento seleccionado no existe.',

            'createdBy.string' => 'El campo creado por debe ser una cadena de texto.',
            'createdBy.max' => 'El campo creado por no puede tener más de 255 caracteres.',
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
            'departmentId' => 'departamento',
            'createdBy' => 'creado por',
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

        if ($this->has('createdBy') && !$this->has('created_by')) {
            $this->merge(['created_by' => $this->input('createdBy')]);
        }

        // Normalize code to uppercase if provided
        if ($this->has('code') && !empty($this->input('code'))) {
            $this->merge([
                'code' => strtoupper(trim($this->input('code')))
            ]);
        }

        // Trim name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->input('name'))
            ]);
        }

        // Set default created_by if not provided
        if (!$this->has('createdBy') && !$this->has('created_by')) {
            $this->merge([
                'created_by' => 'system'
            ]);
        }
    }
}
