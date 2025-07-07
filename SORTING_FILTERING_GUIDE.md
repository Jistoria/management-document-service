# Sorting and Filtering Implementation Guide

## Overview

Se ha implementado soporte completo para sorting y filtering usando `DefaultFiltersRequest` y sus clases derivadas para evitar errores 500 y proporcionar validación robusta.

## ✅ Implementación Completada

### 1. DefaultFiltersRequest (Base)

**Path**: `app/Http/Requests/Globals/DefaultFiltersRequest.php`

**Campos validados**:

```php
'search' => 'nullable|string|max:255'
'page' => 'nullable|integer|min:1'
'per_page' => 'nullable|integer|min:1|max:100'
'sort_by' => 'nullable|string|max:255'
'sort_direction' => 'nullable|string|in:asc,desc'
'format' => 'nullable|string|in:paginate,minimal,dropdown,pluck,collection'
'pluck_key' => 'nullable|string|max:255'
'pluck_label' => 'nullable|string|max:255'
'include' => 'nullable|string|max:500'
// Legacy support
'paginate' => 'nullable|boolean'
'minimal' => 'nullable|boolean'
'pluck' => 'nullable|string|max:255'
```

**Métodos útiles**:

-   `getValidatedFilters()`: Retorna todos los datos validados
-   `getPaginationParams()`: Parámetros de paginación
-   `getSortParams()`: Parámetros de ordenamiento

### 2. Filter Request Classes Específicas

#### FiltersDepartmentRequest

```php
// Campos adicionales específicos
'code' => 'nullable|string|max:20'
'head_office_id' => 'nullable|uuid|exists:head_offices,id'
'created_by' => 'nullable|string|max:255'
```

#### FiltersHeadOfficeRequest

```php
// Campos adicionales específicos
'code' => 'nullable|string|max:20'
'created_by' => 'nullable|string|max:255'
```

#### FiltersCareerRequest

```php
// Campos adicionales específicos
'code' => 'nullable|string|max:20'
'department_id' => 'nullable|uuid|exists:departments,id'
'created_by' => 'nullable|string|max:255'
```

### 3. Services con Sorting

Todos los services ahora incluyen el método `applySorting()`:

```php
private function applySorting($query, array $filters): void
{
    $sortBy = $filters['sort_by'] ?? 'name';
    $sortDirection = $filters['sort_direction'] ?? 'asc';

    // Campos permitidos para ordenamiento
    $allowedSortFields = [
        'name',
        'code',
        'created_at',
        'updated_at',
        'created_by',
        // Campos específicos por entidad
    ];

    if (in_array($sortBy, $allowedSortFields)) {
        $query->orderBy($sortBy, $sortDirection);
    } else {
        $query->orderBy('name', 'asc'); // Fallback
    }
}
```

### 4. Controllers Actualizados

Todos los controllers ahora usan las Filter Request classes:

```php
public function index(FiltersDepartmentRequest $request): JsonResponse
{
    return catchSync(function () use ($request) {
        $filters = $request->getValidatedFilters();

        return ApiIndexBuilder::build(
            $this->departmentService,
            DepartmentResource::class,
            $request,
            $filters
        );
    }, 'Datos obtenidos exitosamente');
}
```

## 🚀 Ejemplos de Uso

### 1. Sorting Básico

```bash
# Ordenar por nombre ascendente (default)
GET /api/departments

# Ordenar por código descendente
GET /api/departments?sort_by=code&sort_direction=desc

# Ordenar por fecha de creación
GET /api/departments?sort_by=created_at&sort_direction=asc
```

### 2. Filtros + Sorting

```bash
# Buscar + ordenar
GET /api/departments?search=sistemas&sort_by=name&sort_direction=asc

# Filtro específico + ordenar
GET /api/departments?head_office_id={uuid}&sort_by=created_at&sort_direction=desc
```

### 3. Formatos + Sorting

```bash
# Paginado + ordenado
GET /api/departments?format=paginate&per_page=10&sort_by=code

# Minimal + ordenado
GET /api/departments?format=minimal&sort_by=name&sort_direction=desc

# Dropdown + ordenado
GET /api/departments?format=dropdown&sort_by=name
```

### 4. Filtros Avanzados

```bash
# Múltiples filtros
GET /api/careers?department_id={uuid}&search=ingeniería&sort_by=created_at&sort_direction=desc

# Con formato específico
GET /api/careers?department_id={uuid}&format=dropdown&sort_by=name
```

## 🛡️ Beneficios de la Validación

### 1. Prevención de Errores 500

-   ✅ Todos los parámetros son `nullable`
-   ✅ Validación de tipos (integer, string, boolean)
-   ✅ Validación de valores permitidos (`in:asc,desc`)
-   ✅ Límites de longitud y rangos

### 2. Campos de Sorting Seguros

```php
// Solo estos campos están permitidos por entidad
$allowedSortFields = [
    'name',           // ✅ Siempre seguro
    'code',           // ✅ Siempre seguro
    'created_at',     // ✅ Timestamp seguro
    'updated_at',     // ✅ Timestamp seguro
    'created_by',     // ✅ String seguro
    'department_id',  // ✅ UUID seguro (solo Career)
];
```

### 3. Fallbacks Automáticos

-   Sort inválido → `name ASC` (fallback)
-   Dirección inválida → `asc` (fallback)
-   Página inválida → validación rechaza request

## 📊 Campos de Sorting por Entidad

### Department

-   `name` ✅
-   `code` ✅
-   `created_at` ✅
-   `updated_at` ✅
-   `created_by` ✅

### HeadOffice

-   `name` ✅
-   `code` ✅
-   `created_at` ✅
-   `updated_at` ✅
-   `created_by` ✅

### Career

-   `name` ✅
-   `code` ✅
-   `created_at` ✅
-   `updated_at` ✅
-   `created_by` ✅
-   `department_id` ✅

## 🔧 Personalización

### Agregar Nuevo Campo de Sorting

1. **Service**: Agregar a `$allowedSortFields`

```php
$allowedSortFields = [
    // ...existing fields...
    'new_field',
];
```

2. **Request**: Agregar validación si es específico

```php
public function rules(): array
{
    return array_merge(parent::rules(), [
        'new_field' => ['nullable', 'string', 'max:255'],
    ]);
}
```

### Agregar Nueva Filter Request

```php
<?php

namespace App\Http\Requests\NewEntity;

use App\Http\Requests\Globals\DefaultFiltersRequest;

class FiltersNewEntityRequest extends DefaultFiltersRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'entity_specific_field' => ['nullable', 'string'],
        ]);
    }
}
```

## ⚠️ Importante

### ❌ NO hacer esto:

```php
// NO: pasar request directo sin validación
$filters = $request->all();

// NO: usar Request genérico
public function index(Request $request)
```

### ✅ SÍ hacer esto:

```php
// SÍ: usar Filter Request específico
public function index(FiltersDepartmentRequest $request)

// SÍ: usar filtros validados
$filters = $request->getValidatedFilters();
```

## 🎯 Resultado Final

-   ✅ **Validación robusta**: Previene errores 500
-   ✅ **Sorting seguro**: Solo campos permitidos
-   ✅ **Filtros tipados**: Validación específica por entidad
-   ✅ **Fallbacks automáticos**: Comportamiento predecible
-   ✅ **Reutilización**: Base común para todas las entidades
-   ✅ **Extensibilidad**: Fácil agregar nuevos campos/entidades

Todos los endpoints ahora manejan sorting y filtering de manera segura y consistente! 🚀
