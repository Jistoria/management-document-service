<?php

namespace App\Http\Requests\Globals;

use App\Http\Requests\BaseFormRequest;

class DefaultFiltersRequest extends BaseFormRequest
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
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'sort_by' => [
                'nullable',
                'string',
                'max:255',
            ],
            'sort_direction' => [
                'nullable',
                'string',
                'in:asc,desc',
            ],
            // Validaciones para ApiIndexBuilder
            'format' => [
                'nullable',
                'string',
                'in:paginate,minimal,dropdown,pluck,collection',
            ],
            'pluck_key' => [
                'nullable',
                'string',
                'max:255',
            ],
            'pluck_label' => [
                'nullable',
                'string',
                'max:255',
            ],
            // Legacy support
            'paginate' => [
                'nullable',
                'boolean',
            ],
            'minimal' => [
                'nullable',
                'boolean',
            ],
            'pluck' => [
                'nullable',
                'string',
                'max:255',
            ],
            'include' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'búsqueda',
            'page' => 'página',
            'per_page' => 'elementos por página',
            'sort_by' => 'ordenar por',
            'sort_direction' => 'dirección de ordenamiento',
            'format' => 'formato',
            'pluck_key' => 'campo clave',
            'pluck_label' => 'campo etiqueta',
            'include' => 'incluir',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'format.in' => 'El formato debe ser uno de: paginate, minimal, dropdown, pluck, collection.',
            'sort_direction.in' => 'La dirección debe ser asc o desc.',
            'per_page.max' => 'No se pueden solicitar más de 100 elementos por página.',
            'per_page.min' => 'Debe solicitar al menos 1 elemento por página.',
            'page.min' => 'La página debe ser mayor a 0.',
        ];
    }

    /**
     * Get validated filters for ApiIndexBuilder
     */
    public function getValidatedFilters(): array
    {
        // Simplemente retornar todos los datos validados
        // Los campos nullable no causan problemas
        return $this->validated();
    }

    /**
     * Get pagination parameters
     */
    public function getPaginationParams(): array
    {
        return [
            'page' => $this->input('page', 1),
            'per_page' => $this->input('per_page', 15),
        ];
    }

    /**
     * Get sorting parameters
     */
    public function getSortParams(): array
    {
        return [
            'sort_by' => $this->input('sort_by'),
            'sort_direction' => $this->input('sort_direction', 'asc'),
        ];
    }
}