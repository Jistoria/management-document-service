<?php

namespace App\Http\Requests\Career;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersCareerRequest extends DefaultFiltersRequest
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
            // Reglas específicas para Career
            'code' => [
                'nullable',
                'string',
                'max:20',
            ],
            'department_id' => [
                'nullable',
                'uuid',
                'exists:departments,id',
            ],
            'created_by' => [
                'nullable',
                'string',
                'max:255',
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
            'department_id' => 'departamento',
            'created_by' => 'creado por',
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'department_id.uuid' => 'El ID del departamento debe ser un UUID válido.',
            'department_id.exists' => 'El departamento seleccionado no existe.',
        ]);
    }
}
