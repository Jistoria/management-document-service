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
            'code' => [
                'nullable',
                'string',
                'max:20',
            ],
            'departmentId' => [
                'nullable',
                'uuid',
                'exists:departments,id',
            ],
            'createdBy' => [
                'nullable',
                'string',
                'max:255',
            ],
            'updatedBy' => [
                'nullable',
                'string',
                'max:255',
            ],
            'has_subsystems' => [
                'boolean',
            ],
            'exclude_subsystem_id' => [
                'nullable',
                'uuid',
            ],
            'subsystem_id' => [
                'nullable',
                'uuid',
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
            'departmentId' => 'departamento',
            'createdBy' => 'creado por',
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'departmentId.uuid' => 'El ID del departamento debe ser un UUID válido.',
            'departmentId.exists' => 'El departamento seleccionado no existe.',
        ]);
    }
}