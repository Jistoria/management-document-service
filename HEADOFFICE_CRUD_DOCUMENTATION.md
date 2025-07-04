# HeadOffice CRUD API Documentation

## 📋 **Resumen**

API completa para gestión de Sedes (Head Offices) usando la función reutilizable `catchSync` para manejo de errores consistente.

## 🏗️ **Arquitectura Implementada**

```
├── Models/
│   └── HeadOffice.php              # Modelo con relaciones y scopes
├── Services/
│   └── HeadOfficeService.php       # Lógica de negocio
├── Http/
│   ├── Controllers/
│   │   └── HeadOfficeController.php # Controlador usando catchSync
│   ├── Requests/HeadOffice/
│   │   ├── StoreHeadOfficeRequest.php   # Validación para crear
│   │   └── UpdateHeadOfficeRequest.php  # Validación para actualizar
│   └── Resources/
│       └── HeadOfficeResource.php   # Transformación de respuestas
└── routes/
    └── api.php                     # Rutas RESTful + adicionales
```

## 🛣️ **Endpoints Disponibles**

### **CRUD Básico**

| Método    | Endpoint                 | Descripción                                       |
| --------- | ------------------------ | ------------------------------------------------- |
| GET       | `/api/head-offices`      | Listar todas las sedes (con paginación y filtros) |
| POST      | `/api/head-offices`      | Crear nueva sede                                  |
| GET       | `/api/head-offices/{id}` | Obtener sede específica                           |
| PUT/PATCH | `/api/head-offices/{id}` | Actualizar sede                                   |
| DELETE    | `/api/head-offices/{id}` | Eliminar sede (soft delete)                       |

### **Endpoints Adicionales**

| Método | Endpoint                            | Descripción                |
| ------ | ----------------------------------- | -------------------------- |
| POST   | `/api/head-offices/{id}/restore`    | Restaurar sede eliminada   |
| GET    | `/api/head-offices/{id}/hierarchy`  | Obtener jerarquía completa |
| GET    | `/api/head-offices/{id}/statistics` | Obtener estadísticas       |
| GET    | `/api/head-offices/code/{code}`     | Buscar por código          |
| POST   | `/api/head-offices/bulk-delete`     | Eliminación masiva         |

## 📝 **Ejemplos de Uso**

### **1. Listar Sedes**

```bash
GET /api/head-offices
```

**Parámetros de consulta:**

-   `per_page` (int): Elementos por página (default: 15)
-   `paginate` (bool): true/false para activar paginación
-   `search` (string): Búsqueda en nombre y código
-   `code` (string): Filtrar por código específico
-   `created_by` (string): Filtrar por usuario creador

**Ejemplo:**

```bash
GET /api/head-offices?search=central&per_page=10&paginate=true
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Sedes obtenidas exitosamente",
    "data": {
        "data": [
            {
                "id": "550e8400-e29b-41d4-a716-446655440000",
                "name": "Sede Central",
                "code": "CENTRAL",
                "created_at": "2025-07-04T10:00:00.000Z",
                "updated_at": "2025-07-04T10:00:00.000Z",
                "created_by": "user123",
                "updated_by": null,
                "version": 1,
                "departments_count": 3,
                "has_departments": true
            }
        ],
        "current_page": 1,
        "per_page": 10,
        "total": 1
    }
}
```

### **2. Crear Sede**

```bash
POST /api/head-offices
Content-Type: application/json

{
    "name": "Sede Norte",
    "code": "NORTE"
}
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Sede creada exitosamente",
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440001",
        "name": "Sede Norte",
        "code": "NORTE",
        "created_at": "2025-07-04T10:30:00.000Z",
        "updated_at": "2025-07-04T10:30:00.000Z",
        "created_by": "user123",
        "updated_by": null,
        "version": 1,
        "departments": []
    }
}
```

**Respuesta de error (validación):**

```json
{
    "success": false,
    "message": {
        "code": ["Ya existe una sede con este código"]
    },
    "errors": null
}
```

### **3. Obtener Sede Específica**

```bash
GET /api/head-offices/550e8400-e29b-41d4-a716-446655440000
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Sede obtenida exitosamente",
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Sede Central",
        "code": "CENTRAL",
        "created_at": "2025-07-04T10:00:00.000Z",
        "updated_at": "2025-07-04T10:00:00.000Z",
        "created_by": "user123",
        "updated_by": null,
        "version": 1,
        "departments": [
            {
                "id": "dept-uuid-1",
                "name": "Sistemas",
                "code": "SIS",
                "created_at": "2025-07-04T10:05:00.000Z",
                "updated_at": "2025-07-04T10:05:00.000Z"
            }
        ]
    }
}
```

### **4. Actualizar Sede**

```bash
PUT /api/head-offices/550e8400-e29b-41d4-a716-446655440000
Content-Type: application/json

{
    "name": "Sede Central Actualizada"
}
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Sede actualizada exitosamente",
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Sede Central Actualizada",
        "code": "CENTRAL",
        "version": 2,
        "updated_at": "2025-07-04T11:00:00.000Z",
        "updated_by": "user123"
    }
}
```

### **5. Eliminar Sede**

```bash
DELETE /api/head-offices/550e8400-e29b-41d4-a716-446655440000
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Sede eliminada exitosamente",
    "data": {
        "deleted": true,
        "id": "550e8400-e29b-41d4-a716-446655440000"
    }
}
```

**Respuesta de error (tiene departamentos):**

```json
{
    "success": false,
    "message": "No se puede eliminar la sede porque tiene departamentos activos.",
    "errors": null
}
```

### **6. Obtener Jerarquía**

```bash
GET /api/head-offices/550e8400-e29b-41d4-a716-446655440000/hierarchy
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Jerarquía obtenida exitosamente",
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Sede Central",
        "departments": [
            {
                "id": "dept-1",
                "name": "Sistemas",
                "careers": [
                    {
                        "id": "career-1",
                        "name": "Ingeniería en Sistemas",
                        "subsystems": [...],
                        "required_documents": [...]
                    }
                ]
            }
        ]
    }
}
```

### **7. Obtener Estadísticas**

```bash
GET /api/head-offices/550e8400-e29b-41d4-a716-446655440000/statistics
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Estadísticas obtenidas exitosamente",
    "data": {
        "departments_count": 3,
        "careers_count": 8,
        "created_at": "2025-07-04T10:00:00.000Z",
        "last_updated": "2025-07-04T11:00:00.000Z",
        "version": 2
    }
}
```

### **8. Buscar por Código**

```bash
GET /api/head-offices/code/CENTRAL
```

### **9. Eliminación Masiva**

```bash
POST /api/head-offices/bulk-delete
Content-Type: application/json

{
    "ids": [
        "550e8400-e29b-41d4-a716-446655440001",
        "550e8400-e29b-41d4-a716-446655440002"
    ]
}
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Eliminación masiva completada",
    "data": {
        "deleted_count": 1,
        "total_requested": 2
    }
}
```

## ⚡ **Características Implementadas**

### **✅ Uso de catchSync**

Todos los endpoints usan la función `catchSync` para:

-   Manejo consistente de errores
-   Logging automático de excepciones
-   Respuestas JSON estandarizadas
-   Códigos de estado HTTP apropiados

### **✅ Validaciones Robustas**

-   **Request Classes** para validación de entrada
-   **Uniqueness validation** con soft deletes
-   **Format validation** para códigos
-   **Business logic validation** en Service

### **✅ Service Layer**

-   Separación clara de responsabilidades
-   Validaciones de negocio
-   Operaciones complejas encapsuladas
-   Reutilización de código

### **✅ Resource Transformation**

-   Respuestas consistentes
-   Conditional loading de relaciones
-   Metadata adicional
-   Formato estandarizado

### **✅ Características Avanzadas**

-   **Soft Deletes** con restauración
-   **Audit Fields** automáticos
-   **Versioning** incremental
-   **Bulk operations**
-   **Search & Filtering**
-   **Pagination**

## 🔧 **Configuración Adicional**

### **Middleware (Recomendado)**

```php
// Para autenticación y autorización
Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
    // Rutas de head-offices
});
```

### **Logging Personalizado**

El `catchSync` ya incluye logging automático, pero puedes personalizar:

```php
// En HeadOfficeService
Log::info('Head office created', [
    'id' => $headOffice->id,
    'created_by' => $headOffice->created_by
]);
```

## 🎯 **Beneficios Obtenidos**

1. **🔄 Consistencia**: Todas las respuestas siguen el mismo formato
2. **🛡️ Robustez**: Manejo completo de errores y validaciones
3. **📊 Trazabilidad**: Logging automático de todas las operaciones
4. **🚀 Performance**: Eager loading optimizado y paginación
5. **🔧 Mantenibilidad**: Código bien estructurado y reutilizable
6. **📋 Documentación**: APIs autodocumentadas con responses claras

---

¡CRUD completo implementado con `catchSync` y mejores prácticas! 🎉
