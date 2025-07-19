<?php

namespace App\Http\Requests\Subsystem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubsystemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subsystemId = $this->route('subsystem');
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:255', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('subsystems', 'code')->ignore($subsystemId)->whereNull('deleted_at')],
            'updatedBy' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede exceder los 255 caracteres.',
            'code.regex' => 'El código solo puede contener letras mayúsculas, números, guiones y guiones bajos.',
            'code.unique' => 'Ya existe un subsistema con este código.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'code' => 'código',
            'updatedBy' => 'actualizado por',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code') && $this->input('code')) {
            $this->merge(['code' => strtoupper($this->input('code'))]);
        }
        if ($this->has('name')) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
        if ($this->has('updatedBy') && !$this->has('updated_by')) {
            $this->merge(['updated_by' => $this->input('updatedBy')]);
        }
    }
}
