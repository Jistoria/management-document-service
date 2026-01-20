<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de filtros para endpoints públicos
 * 
 * Limita los filtros disponibles para evitar exposición de datos sensibles
 * y asegura que solo se acceda a registros activos
 */
class PublicFiltersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Acceso público sin autenticación
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Búsqueda general
            'search' => 'sometimes|string|max:100',
            'code' => 'sometimes|string|max:20',
            
            // Filtros relacionales
            'head_office_id' => 'sometimes|uuid',
            'headOfficeId' => 'sometimes|uuid',
            'department_id' => 'sometimes|uuid',
            'departmentId' => 'sometimes|uuid',
            'process_category_id' => 'sometimes|uuid',
            'processCategoryId' => 'sometimes|uuid',
            
            // Formato de respuesta
            'format' => 'sometimes|in:collection,minimal,dropdown,pluck,paginate',
            'perPage' => 'sometimes|integer|min:1|max:50', // Limitar a 50 para evitar sobrecarga
            'per_page' => 'sometimes|integer|min:1|max:50',
            
            // Parámetros para pluck
            'pluckKey' => 'sometimes|string|in:id,code',
            'pluck_key' => 'sometimes|string|in:id,code',
            'pluckLabel' => 'sometimes|string|in:name,code',
            'pluck_label' => 'sometimes|string|in:name,code',
            
            // Paginación
            'page' => 'sometimes|integer|min:1',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'search' => 'búsqueda',
            'code' => 'código',
            'head_office_id' => 'ID de sede',
            'department_id' => 'ID de departamento',
            'format' => 'formato',
            'perPage' => 'registros por página',
        ];
    }

    /**
     * Obtiene filtros seguros pre-procesados para uso público
     * 
     * @return array<string, mixed>
     */
    public function getPublicFilters(): array
    {
        $filters = [];
        
        // Solo filtros permitidos explícitamente
        $allowedFilters = [
            'search',
            'code',
            'head_office_id',
            'headOfficeId',
            'department_id',
            'departmentId',
            'process_category_id',
            'processCategoryId',
        ];
        
        foreach ($allowedFilters as $filter) {
            if ($this->filled($filter)) {
                // Convertir camelCase a snake_case para compatibilidad interna
                $internalFilter = match($filter) {
                    'headOfficeId' => 'head_office_id',
                    'departmentId' => 'department_id',
                    'processCategoryId' => 'process_category_id',
                    default => $filter
                };
                
                $filters[$internalFilter] = $this->input($filter);
            }
        }
        
        // CRÍTICO: Forzar solo registros activos para seguridad
        // Esto evita exponer registros eliminados o inactivos
        $filters['is_active'] = true;
        
        return $filters;
    }

    /**
     * Obtiene el formato de respuesta solicitado
     */
    public function getResponseFormat(): string
    {
        return $this->input('format', 'collection');
    }

    /**
     * Obtiene el límite de paginación con un máximo seguro
     */
    public function getPerPage(int $default = 20): int
    {
        $perPage = $this->input('perPage') ?? $this->input('per_page', $default);
        
        // Asegurar que no exceda el máximo permitido
        return min((int) $perPage, 50);
    }
}
