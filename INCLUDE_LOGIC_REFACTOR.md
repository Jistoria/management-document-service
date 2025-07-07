# Resolución del Problema: Duplicación de Lógica en Includes

## 🎯 **Problema Resuelto**

**Antes**: Lógica duplicada entre `resolveIncludes()` (Service) y `shouldInclude()` (Resource)
**Después**: Una sola fuente de verdad - `resolveIncludes()` en Service + `relationLoaded()` en Resource

## ✅ **Solución Implementada**

### **Principio: Single Source of Truth**

1. **Service** (`resolveIncludes`):

    - ✅ **ÚNICA** fuente de decisión sobre qué includes procesar
    - ✅ Carga físicamente las relaciones necesarias
    - ✅ Marca qué includes fueron solicitados en `_requested_includes`

2. **Resource** (`wasIncludeRequested` + `relationLoaded`):
    - ✅ Solo verifica si el include fue solicitado **Y** la relación fue cargada
    - ✅ No analiza el parámetro `include` directamente
    - ✅ No ejecuta queries adicionales

## 🔧 **Patrón Implementado**

### **En el Service:**

```php
public function resolveIncludes(array $requestedIncludes, $model): void
{
    $resolved = [];
    $context = []; // Store what was requested

    foreach ($requestedIncludes as $include) {
        $include = trim($include);

        match ($include) {
            'departments' => $resolved[] = 'departments',
            'statistics' => $resolved[] = 'departments', // Load needed relationships
            'hierarchy' => $resolved = array_merge($resolved, [
                'departments.careers.subsystems',
            ]),
            default => null,
        };

        $context[] = $include; // Store original request
    }

    // Load relationships
    if (!empty($resolved)) {
        $model->load(array_unique($resolved));
    }

    // Store context for resource
    $model->setAttribute('_requested_includes', $context);
}
```

### **En el Resource:**

```php
'departments' => $this->when(
    $this->relationLoaded('departments') && $this->wasIncludeRequested('departments'),
    function () {
        return DepartmentResource::collection($this->departments);
    }
),

'statistics' => $this->when(
    $this->relationLoaded('departments') && $this->wasIncludeRequested('statistics'),
    function () {
        return [
            'departmentsCount' => $this->departments->count(), // No query - ya cargado
            'careersCount' => $this->departments->sum(function ($dept) {
                return $dept->relationLoaded('careers') ? $dept->careers->count() : 0;
            }),
        ];
    }
),

protected function wasIncludeRequested(string $include): bool
{
    $requestedIncludes = $this->resource->getAttribute('_requested_includes') ?? [];
    return in_array($include, $requestedIncludes);
}
```

## 📋 **Archivos Actualizados**

### **Services - `resolveIncludes()` mejorado:**

-   ✅ `app/Services/HeadOfficeService.php`
-   ✅ `app/Services/DepartmentService.php`
-   ✅ `app/Services/CareerService.php`

### **Resources - `shouldInclude()` → `wasIncludeRequested() + relationLoaded()`:**

-   ✅ `app/Http/Resources/HeadOfficeResource.php`
-   ✅ `app/Http/Resources/DepartmentResource.php`
-   ✅ `app/Http/Resources/CareerResource.php`

### **Base - Métodos deprecados:**

-   ✅ `app/Http/Resources/BaseResource.php` - `shouldInclude()` marcado como `@deprecated`

## 🎯 **Beneficios Obtenidos**

### **1. Eliminación de Duplicación:**

-   ❌ **Antes**: Ambos analizaban el parámetro `include`
-   ✅ **Después**: Solo Service analiza, Resource confía en Service

### **2. Prevención de N+1 Queries:**

-   ❌ **Antes**: Resource podía ejecutar queries si Service no cargó
-   ✅ **Después**: Resource solo usa datos ya cargados

### **3. Consistencia Garantizada:**

-   ❌ **Antes**: Service e Resource podían estar desincronizados
-   ✅ **Después**: Resource solo muestra lo que Service preparó

### **4. Mantenimiento Simplificado:**

-   ❌ **Antes**: Cambios en dos lugares (Service + Resource)
-   ✅ **Después**: Cambios solo en Service

## 🧪 **Casos de Prueba**

### **Caso 1: `?include=departments`**

-   Service: Carga `departments`
-   Resource: Muestra `departments` (cargado ✅ + solicitado ✅)
-   Resource: NO muestra `statistics` (no solicitado ❌)

### **Caso 2: `?include=statistics`**

-   Service: Carga `departments` (necesario para statistics)
-   Resource: NO muestra `departments` (no solicitado ❌)
-   Resource: Muestra `statistics` (cargado ✅ + solicitado ✅)

### **Caso 3: `?include=departments,statistics`**

-   Service: Carga `departments`
-   Resource: Muestra `departments` (cargado ✅ + solicitado ✅)
-   Resource: Muestra `statistics` (cargado ✅ + solicitado ✅)

## 🚀 **Resultado Final**

-   ✅ **Cero duplicación de lógica**
-   ✅ **Cero N+1 queries**
-   ✅ **Consistencia garantizada**
-   ✅ **Mantenimiento simplificado**
-   ✅ **Backward compatibility preservada**

El sistema ahora tiene una arquitectura limpia donde **Service decide qué cargar** y **Resource decide qué mostrar** basándose únicamente en lo que Service preparó.
