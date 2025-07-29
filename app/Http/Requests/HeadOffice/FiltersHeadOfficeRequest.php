<?php

namespace App\Http\Requests\HeadOffice;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersHeadOfficeRequest extends DefaultFiltersRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Cambiar a true para permitir el acceso
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            // Reglas específicas para HeadOffice
            'code' => [
                'nullable',
                'string',
                'max:20',
            ],
            'created_by' => [
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
}
