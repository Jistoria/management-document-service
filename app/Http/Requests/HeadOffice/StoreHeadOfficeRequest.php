<?php

namespace App\Http\Requests\HeadOffice;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreHeadOfficeRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // In a real app, implement proper authorization
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('head_offices', 'name')->whereNull('deleted_at')
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('head_offices', 'code')->whereNull('deleted_at')
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'name.string' => 'El nombre debe ser una cadena de texto',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'name.unique' => 'Ya existe una sede con este nombre',

            'code.required' => 'El código es requerido',
            'code.string' => 'El código debe ser una cadena de texto',
            'code.max' => 'El código no puede exceder 255 caracteres',
            'code.regex' => 'El código solo puede contener letras mayúsculas, números, guiones y guiones bajos',
            'code.unique' => 'Ya existe una sede con este código'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'code' => 'código'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert code to uppercase
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim($this->code))
            ]);
        }

        // Trim name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name)
            ]);
        }
    }
}