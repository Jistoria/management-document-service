<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for creating a new department
 */
class StoreDepartmentRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'headOfficeId' => [
                'required',
                'uuid',
                'exists:head_offices,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('departments', 'name')
                    ->where('head_office_id', $this->getHeadOfficeId())
                    ->whereNull('deleted_at')
            ],
            'code' => [
                'nullable',
                'string',
                'max:255',
                'alpha_num',
                'uppercase',
                Rule::unique('departments', 'code')
                    ->whereNull('deleted_at')
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'headOfficeId.required' => 'La sede es requerida.',
            'headOfficeId.uuid' => 'El ID de sede debe ser un UUID válido.',
            'headOfficeId.exists' => 'La sede seleccionada no existe.',
            'name.required' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.unique' => 'Ya existe un departamento con este nombre en la sede seleccionada.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede exceder los 255 caracteres.',
            'code.alpha_num' => 'El código solo puede contener letras y números.',
            'code.uppercase' => 'El código debe estar en mayúsculas.',
            'code.unique' => 'Ya existe un departamento con este código.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'headOfficeId' => 'sede',
            'name' => 'nombre',
            'code' => 'código',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Handle camelCase to snake_case conversion for compatibility
        if ($this->has('headOfficeId') && !$this->has('head_office_id')) {
            $this->merge(['head_office_id' => $this->input('headOfficeId')]);
        }

        // Handle code uppercase conversion
        if ($this->has('code') && $this->input('code')) {
            $this->merge([
                'code' => strtoupper($this->input('code'))
            ]);
        }
    }

    /**
     * Get head office ID supporting both camelCase and snake_case
     */
    protected function getHeadOfficeId(): ?string
    {
        return $this->input('headOfficeId') ?? $this->input('head_office_id');
    }
}
