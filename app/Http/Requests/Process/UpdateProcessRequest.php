<?php

namespace App\Http\Requests\Process;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateProcessRequest extends BaseFormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $processId = $this->route('process');

        return [
            'processCategoryId' => [
                'sometimes',
                'required',
                'string',
                'uuid',
                'exists:process_categories,id'
            ],
            'parentId' => [
                'nullable',
                'string',
                'uuid',
                'exists:processes,id',
                // Evitar autoreferencia
                function ($attribute, $value, $fail) use ($processId) {
                    if ($value === $processId) {
                        $fail('Un proceso no puede ser padre de sí mismo.');
                    }
                }
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('processes', 'code')->ignore($processId)->whereNull('deleted_at')
            ],
            'order' => [
                'sometimes',
                'required',
                'integer',
                'min:0'
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'process_category_id' => 'categoría de proceso',
            'parent_id' => 'proceso padre',
            'name' => 'nombre',
            'code' => 'código',
            'order' => 'orden',
        ];
    }
}
