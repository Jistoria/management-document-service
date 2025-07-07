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
            'department_id' => [
                'sometimes',
                'required',
                'uuid',
                'exists:departments,id',
            ],
            'updated_by' => [
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

            'department_id.required' => 'El departamento es requerido.',
            'department_id.uuid' => 'El ID del departamento debe ser un UUID válido.',
            'department_id.exists' => 'El departamento seleccionado no existe.',

            'updated_by.string' => 'El campo actualizado por debe ser una cadena de texto.',
            'updated_by.max' => 'El campo actualizado por no puede tener más de 255 caracteres.',

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
            'department_id' => 'departamento',
            'updated_by' => 'actualizado por',
            'version' => 'versión',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
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

        // Set default updated_by if not provided
        if (!$this->has('updated_by') || empty($this->input('updated_by'))) {
            $this->merge([
                'updated_by' => 'system'
            ]);
        }
    }
}
