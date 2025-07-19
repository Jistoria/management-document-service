<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersDepartmentRequest extends DefaultFiltersRequest
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
            'headOfficeId' => [
                'nullable',
                'uuid',
                'exists:head_offices,id',
            ],
            'createdBy' => [
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
            'headOfficeId' => 'sede principal',
            'createdBy' => 'creado por',
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'headOfficeId.uuid' => 'El ID de la sede debe ser un UUID válido.',
            'headOfficeId.exists' => 'La sede seleccionada no existe.',
        ]);
    }
}
