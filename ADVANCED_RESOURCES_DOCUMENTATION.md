# Sistema de Resources Avanzado - Documentación Completa

## 🎯 Resumen

Se ha implementado un **sistema de resources extendible y flexible** que facilita la transformación de datos para diferentes casos de uso: paginación, colecciones simples, dropdowns, plucks, vistas detalladas y minimales.

## 🏗️ Arquitectura del Sistema

### BaseResource (Clase Base Extendible)

-   ✅ **Métodos estáticos para transformaciones**: `paginated()`, `simpleCollection()`, `pluck()`, `dropdown()`
-   ✅ **Métodos de instancia**: `detailed()`, `minimal()`, `withContext()`
-   ✅ **Sistema de includes dinámico**: `shouldInclude()`, `getRequestedIncludes()`
-   ✅ **Metadata automática**: `getDetailedMeta()`, `getResourceType()`

### Resources Implementados

1. **HeadOfficeResource** - Extendido con BaseResource
2. **DepartmentResource** - Soporte completo para relaciones
3. **CareerResource** - Jerarquía de relaciones
4. **PaginateResource** - Mejorado con links y metadata

## 📋 Funcionalidades por Endpoint

### 1. **Listado de Sedes (GET /api/head-offices)**

#### 🔹 **Colección Simple (Default)**

```http
GET /api/head-offices
```

**Respuesta:**

```json
{
    "success": true,
    "message": "Sedes obtenidas exitosamente",
    "data": {
        "data": [
            {
                "id": "uuid",
                "name": "Sede Central",
                "code": "CENTRAL",
                "created_at": "2025-07-04T22:36:25.000000Z",
                "updated_at": "2025-07-04T22:36:47.000000Z",
                "created_by": "system",
                "updated_by": "system",
                "version": 2,
                "departments_count": 0
            }
        ],
        "count": 1
    }
}
```

#### 🔹 **Paginación**

```http
GET /api/head-offices?paginate=true&per_page=5
```

**Respuesta:**

```json
{
  "success": true,
  "message": "Sedes obtenidas exitosamente",
  "data": {
    "data": [...],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 5,
      "total": 1,
      "from": 1,
      "to": 1,
      "has_more_pages": false
    }
  }
}
```

#### 🔹 **Vista Minimal**

```http
GET /api/head-offices?minimal=true
```

**Respuesta:**

```json
{
    "success": true,
    "message": "Sedes obtenidas exitosamente",
    "data": {
        "data": [
            {
                "id": "uuid",
                "name": "Sede Central",
                "code": "CENTRAL",
                "departments_count": 0
            }
        ],
        "count": 1
    }
}
```

#### 🔹 **Formato Dropdown**

```http
GET /api/head-offices?format=dropdown
```

**Respuesta:**

```json
{
    "success": true,
    "message": "Sedes obtenidas exitosamente",
    "data": {
        "options": [
            {
                "value": "uuid",
                "label": "Sede Central",
                "code": "CENTRAL"
            }
        ],
        "count": 1
    }
}
```

#### 🔹 **Formato Pluck**

```http
GET /api/head-offices?pluck=id&pluck_label=name
```

**Respuesta:**

```json
{
    "success": true,
    "message": "Sedes obtenidas exitosamente",
    "data": {
        "data": [
            {
                "value": "uuid",
                "label": "Sede Central"
            }
        ],
        "count": 1
    }
}
```

### 2. **Detalle de Sede (GET /api/head-offices/{id})**

#### 🔹 **Vista Detallada**

```http
GET /api/head-offices/{id}
```

**Respuesta:**

```json
{
    "success": true,
    "message": "Sede obtenida exitosamente",
    "data": {
        "id": "uuid",
        "name": "Sede Central",
        "code": "CENTRAL",
        "created_at": "2025-07-04T22:36:25.000000Z",
        "updated_at": "2025-07-04T22:36:47.000000Z",
        "created_by": "system",
        "updated_by": "system",
        "version": 2,
        "departments": {},
        "departments_count": 0,
        "statistics": {},
        "hierarchy": {},
        "meta": {
            "resource_type": "head_office",
            "generated_at": "2025-07-04T22:57:15.858919Z",
            "context": []
        }
    }
}
```

#### 🔹 **Con Includes Específicos**

```http
GET /api/head-offices/{id}?include=departments,statistics
```

### 3. **Filtros Disponibles**

#### 🔹 **Búsqueda por Texto**

```http
GET /api/head-offices?search=central
```

#### 🔹 **Filtro por Código**

```http
GET /api/head-offices?code=CENTRAL
```

#### 🔹 **Filtro por Creador**

```http
GET /api/head-offices?created_by=system
```

#### 🔹 **Combinando Filtros**

```http
GET /api/head-offices?search=sede&paginate=true&per_page=10&minimal=true
```

## 🛠️ Clases y Métodos Disponibles

### BaseResource

#### Métodos Estáticos

```php
// Paginación
BaseResource::paginated(LengthAwarePaginator $paginator): array

// Colección simple
BaseResource::simpleCollection(Collection $collection): array

// Formato pluck (key-value)
BaseResource::pluck(Collection $collection, string $valueKey, string $labelKey): array

// Formato dropdown/select
BaseResource::dropdown(Collection $collection, string $valueKey, string $labelKey): array
```

#### Métodos de Instancia

```php
// Vista detallada con metadata
$resource->detailed(): array

// Vista minimal (campos básicos)
$resource->minimal(): array

// Establecer contexto
$resource->withContext(array $context): static

// Verificar si incluir relación
$resource->shouldInclude(string $relation, Request $request): bool
```

### HeadOfficeResource

#### Métodos Específicos

```php
// Formato dropdown específico para HeadOffice
HeadOfficeResource::forDropdown(Collection $collection): array

// Vista con jerarquía completa
$headOfficeResource->withHierarchy(): array
```

### PaginateResource

#### Uso Mejorado

```php
// Crear respuesta paginada con resource específico
PaginateResource::paginate(LengthAwarePaginator $paginator, string $resourceClass): PaginateResource

// Constructor con clase de resource
new PaginateResource($paginator, HeadOfficeResource::class)
```

## 🎯 Casos de Uso

### 1. **Frontend Listings**

-   **Paginación**: Para tablas con muchos registros
-   **Minimal**: Para listas simples y rápidas
-   **Simple Collection**: Para cargas completas

### 2. **Formularios y Selects**

-   **Dropdown**: Para campos select/combobox
-   **Pluck**: Para autocomplete y búsquedas

### 3. **Dashboards**

-   **Statistics**: Información resumida
-   **Hierarchy**: Estructura organizacional

### 4. **APIs Mobile**

-   **Minimal**: Reducir transferencia de datos
-   **Includes dinámicos**: Cargar solo lo necesario

## 🔧 Configuración de Includes

### Query Parameters

```http
# Incluir departamentos
?include=departments

# Incluir múltiples relaciones
?include=departments,statistics,hierarchy

# Incluir todo
?include=*
```

### Programático

```php
$resource = new HeadOfficeResource($headOffice);
$resource->withContext(['include_relations' => ['departments', 'careers']]);
```

## 📊 Beneficios Implementados

### ✅ **Flexibilidad**

-   Múltiples formatos de respuesta desde un mismo endpoint
-   Configuración dinámica via query parameters
-   Sistema de includes granular

### ✅ **Reusabilidad**

-   BaseResource extendible para cualquier entidad
-   Métodos compartidos entre resources
-   Patterns consistentes

### ✅ **Performance**

-   Vistas minimales para reducir payload
-   Carga condicional de relaciones
-   Caché-friendly con metadata

### ✅ **Mantenibilidad**

-   Separación clara de responsabilidades
-   Documentación automática via metadata
-   Fácil extensión para nuevos casos

### ✅ **Developer Experience**

-   API consistente y predecible
-   Múltiples opciones desde un endpoint
-   Documentación clara con ejemplos

## 🚀 Próximas Mejoras Sugeridas

1. **Cache Layer**: Implementar caché para responses frecuentes
2. **GraphQL-like**: Sistema de fields dinámicos (?fields=id,name,code)
3. **Export Formats**: CSV, Excel, PDF desde los mismos resources
4. **Validation**: Validar includes y formatos solicitados
5. **Rate Limiting**: Por tipo de formato y complejidad

---

**✅ Sistema de Resources completamente implementado y funcional con soporte para todos los casos de uso requeridos.**
