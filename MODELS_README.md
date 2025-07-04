# Laravel Models - Management Document Service

## 📊 **Resumen de Modelos Generados**

Este documento describe todos los modelos Laravel generados para el microservicio de gestión documental.

### 🏗️ **Organización del Código**

```
app/
├── Models/
│   ├── AcademicRole.php           # Roles académicos
│   ├── Career.php                 # Carreras académicas
│   ├── Department.php             # Departamentos
│   ├── DocumentType.php           # Tipos de documentos
│   ├── ExternalApi.php            # Configuración APIs externas
│   ├── HeadOffice.php             # Sedes principales
│   ├── MetadataField.php          # Campos de metadatos
│   ├── MetadataSchema.php         # Esquemas de metadatos
│   ├── MetadataSchemaEvent.php    # Eventos del sistema
│   ├── Process.php                # Procesos
│   ├── ProcessCategory.php        # Categorías de procesos
│   ├── ProcessHierarchy.php       # Vista materializada
│   ├── RequiredDocument.php       # Documentos requeridos
│   ├── StorageUnit.php            # Unidades de almacenamiento
│   ├── StorageUnitType.php        # Tipos de unidades
│   └── Subsystem.php              # Subsistemas
└── Traits/
    └── HasAuditFields.php         # Trait para auditoría
```

## 🔧 **Características Implementadas**

### **✅ Traits y Funcionalidades Base**

-   **HasAuditFields**: Auditoría automática con usuarios externos
-   **HasUuids**: IDs UUID para todas las entidades principales
-   **SoftDeletes**: Eliminación lógica en modelos críticos
-   **Timestamps**: Gestión automática de created_at/updated_at

### **✅ Relaciones Completas**

-   **Jerarquías**: Procesos y unidades de almacenamiento soportan parent-child
-   **Many-to-Many**: Carreras ↔ Subsistemas
-   **Foreign Keys**: Todas las relaciones con constraints apropiados
-   **Scopes**: Métodos de consulta optimizados en todos los modelos

### **✅ Validaciones y Business Logic**

-   **Validación de tipos de datos**: En MetadataField
-   **Constraints de negocio**: Verificaciones en modelos
-   **Formateo automático**: Valores por defecto tipados
-   **Path building**: Construcción de rutas jerárquicas

### **✅ Integración de Microservicios**

-   **Referencias externas**: external_user_id, external_organization_id
-   **Configuración de APIs**: Modelo ExternalApi completo
-   **Eventos distribuidos**: Correlation IDs para tracing
-   **Versionado**: Control de versiones automático

## 📊 **Modelos por Categoría**

### **🏢 Organización Académica**

1. **HeadOffice** - Sedes principales
2. **Department** - Departamentos
3. **Career** - Carreras académicas
4. **Subsystem** - Subsistemas del sistema

### **📋 Gestión de Procesos**

5. **ProcessCategory** - Categorías de procesos
6. **Process** - Procesos específicos (con jerarquía)
7. **ProcessHierarchy** - Vista materializada optimizada

### **📄 Gestión Documental**

8. **DocumentType** - Tipos de documentos
9. **AcademicRole** - Roles académicos
10. **RequiredDocument** - Documentos requeridos por proceso

### **🗄️ Almacenamiento**

11. **StorageUnitType** - Tipos de unidades de almacenamiento
12. **StorageUnit** - Unidades específicas (con jerarquía)

### **📊 Sistema de Metadatos**

13. **MetadataSchema** - Esquemas con versionado e herencia
14. **MetadataField** - Campos con validación y OCR
15. **MetadataSchemaEvent** - Auditoría de eventos

### **🌐 Microservicios**

16. **ExternalApi** - Configuración de APIs externas

## 🔍 **Funcionalidades Destacadas**

### **🏗️ Jerarquías Inteligentes**

```php
// Obtener jerarquía completa de procesos
$process = Process::find($id);
$ancestors = $process->getAncestors();
$descendants = $process->getDescendants();
$fullPath = $process->getFullPath();

// Vista materializada optimizada
$hierarchy = ProcessHierarchy::byProcessCategory($categoryId)
                            ->orderedHierarchy()
                            ->get();
```

### **📊 Metadatos Flexibles**

```php
// Validar valor contra esquema
$field = MetadataField::find($id);
$isValid = $field->validateValue($userInput);

// Obtener campos heredados
$schema = MetadataSchema::find($id);
$allFields = $schema->getAllFields(); // Incluye herencia
```

### **🔗 Integración Externa**

```php
// Configurar API externa
$api = ExternalApi::byServiceName('auth-service')->active()->first();
$config = $api->getHttpClientConfig();
$headers = $api->getAuthHeaders($token);

// Tracking distribuido
MetadataSchemaEvent::createEvent(
    $schemaId,
    'schema_updated',
    correlationId: $correlationId
);
```

### **📋 Consultas Optimizadas**

```php
// Documentos requeridos por proceso y rol
$documents = RequiredDocument::byProcess($processId)
                            ->byAcademicRole($roleId)
                            ->mandatory()
                            ->ordered()
                            ->with(['documentType', 'academicRole'])
                            ->get();

// Carreras activas con jerarquía completa
$careers = Career::active()
                ->with(['department.headOffice', 'subsystems'])
                ->get();
```

## 🚀 **Uso Recomendado**

### **1. Para Consultas Simples**

```php
// Usar scopes para filtrado
$activeDocuments = DocumentType::active()->get();
$mandatoryDocs = RequiredDocument::mandatory()->get();
```

### **2. Para Jerarquías**

```php
// Usar vista materializada para performance
$hierarchy = ProcessHierarchy::byProcessCategory($id)->get();

// Usar métodos de modelo para navegación
$children = $process->getDirectChildren();
```

### **3. Para Auditoría**

```php
// El trait HasAuditFields maneja automáticamente:
// - created_by / updated_by
// - version increment
// - timestamps
```

### **4. Para Integración**

```php
// Usar ExternalApi para configuración
$userService = ExternalApi::byServiceName('user-service')->first();
$response = Http::withOptions($userService->getHttpClientConfig())
               ->withHeaders($userService->getAuthHeaders($token))
               ->get($userService->buildUrl('/users'));
```

## ✅ **Beneficios Obtenidos**

1. **🎯 Microservicio Puro**: Sin dependencias de autenticación
2. **🚀 Performance**: Vistas materializadas y índices optimizados
3. **🔒 Seguridad**: Validaciones y constraints robustos
4. **📊 Escalabilidad**: Preparado para grandes volúmenes de datos
5. **🔗 Integrable**: Listo para comunicación entre microservicios
6. **📋 Auditable**: Sistema completo de tracking y versionado
7. **🛠️ Mantenible**: Código bien organizado y documentado

---

**Total: 16 modelos + 1 trait** - ¡Base de datos completamente modelada! 🎉
