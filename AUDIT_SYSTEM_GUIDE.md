# Sistema de Auditoría Laravel - Guía de Implementación

## 🔧 **Configuración Inicial**

### 1. **Aplicar a Modelos**

```php
// En cualquier modelo que necesite auditoría
use App\Traits\Auditable;

class HeadOffice extends Model
{
    use HasUuids, SoftDeletes, Auditable; // 👈 Agregar trait

    // ... resto del modelo
}
```

### 2. **Registrar Middleware**

```php
// En app/Http/Kernel.php
protected $middleware = [
    // ... otros middlewares
    \App\Http\Middleware\CaptureAuditContext::class,
];
```

## 🎯 **Cómo Funciona**

### **Auditoría Automática**

```php
// ✅ Se audita automáticamente
$office = HeadOffice::create([
    'name' => 'Nueva Sede',
    'code' => 'NS-001'
]);
// Crea registro en audit_logs: action='INSERT'

$office->update(['name' => 'Sede Actualizada']);
// Crea registro en audit_logs: action='UPDATE'

$office->delete();
// Crea registro en audit_logs: action='SOFT_DELETE'
```

### **Auditoría Manual con Razón**

```php
// ✅ Auditoría manual con contexto específico
$document = RequiredDocument::find($id);
$document->markAsArchived('Documento obsoleto por nueva normativa');
// Guarda la razón específica en change_reason
```

## 📋 **Información Capturada**

### **Usuario Autenticado (Laravel Auth)**

```php
// Si user está autenticado:
'user_id' => '123',
'external_user_id' => '123',
'user_email' => 'usuario@ejemplo.com',
'user_name' => 'Juan Pérez'
```

### **Usuario desde Microservicio**

```php
// Headers esperados:
X-User-Id: external-user-123
X-External-User-Id: external-user-123
X-User-Email: usuario@microservicio.com
X-User-Name: Juan Pérez
X-Correlation-ID: mgmt-doc-20250707-abc123
```

### **Contexto de Request**

```php
'ip_address' => '192.168.1.100',
'user_agent' => 'Mozilla/5.0...',
'endpoint' => 'https://api.ejemplo.com/head-offices',
'session_id' => 'sess_abc123',
'correlation_id' => 'mgmt-doc-20250707-abc123'
```

### **Metadatos Detallados**

```json
{
    "change_type": "update",
    "fields_changed_count": 2,
    "field_details": {
        "name": {
            "from": "Sede Antigua",
            "to": "Sede Nueva",
            "changed": true
        },
        "version": {
            "from": "1",
            "to": "2",
            "changed": true
        }
    },
    "summary": "Actualización en tabla head_offices con 2 campos modificados"
}
```

## 🔍 **Consultas de Auditoría**

### **Buscar por Usuario**

```php
// Todos los cambios de un usuario
$audits = AuditLog::forUser('external-user-123')
    ->recent(48) // últimas 48 horas
    ->get();
```

### **Buscar por Tabla**

```php
// Todos los cambios en head_offices
$audits = AuditLog::forTable('head_offices')
    ->forAction('UPDATE')
    ->orderBy('created_at', 'desc')
    ->get();
```

### **Cambios de un Registro Específico**

```php
// Historia de cambios de un registro
$audits = AuditLog::where('table_name', 'head_offices')
    ->where('record_id', $headOfficeId)
    ->orderBy('created_at', 'desc')
    ->get();
```

### **Vistas Predefinidas**

```sql
-- Cambios recientes (últimas 24h)
SELECT * FROM v_recent_changes;

-- Resumen por usuario
SELECT * FROM v_audit_summary_by_user
WHERE external_user_id = 'user-123';
```

## 🚀 **Ventajas vs Triggers de DB**

### ✅ **Control Total en Laravel**

-   Acceso directo a usuario autenticado
-   Integración con middleware y servicios
-   Manejo de errores sin afectar operación principal
-   Flexibilidad para lógica de negocio

### ✅ **Información Rica**

-   Contexto completo de request HTTP
-   Trazabilidad entre microservicios
-   Metadatos personalizados por modelo
-   Razones específicas de cambios

### ✅ **Performance**

-   Solo se audita cuando es necesario
-   No afecta transacciones críticas
-   Manejo asíncrono posible

## 🔧 **Configuración Avanzada**

### **Deshabilitar Auditoría Temporalmente**

```php
// Para operaciones masivas
RequiredDocument::withoutAuditing(function () {
    RequiredDocument::where('status', 'old')->delete();
});
```

### **Auditoría Asíncrona**

```php
// En el trait, cambiar a job
dispatch(new CreateAuditLogJob($auditData));
```

### **Filtros Personalizados**

```php
// Solo auditar campos importantes
protected function shouldAuditField(string $field): bool
{
    $ignoredFields = ['updated_at', 'last_login'];
    return !in_array($field, $ignoredFields);
}
```

## 🎯 **Casos de Uso**

1. **Compliance y Regulación**: Rastrea quién cambió qué y cuándo
2. **Debugging**: Identifica cuándo y cómo se introdujeron problemas
3. **Rollback**: Información para revertir cambios específicos
4. **Reportes**: Análisis de actividad de usuarios y sistemas
5. **Trazabilidad**: Seguimiento entre microservicios

Este sistema proporciona auditoría empresarial completa sin la rigidez de triggers de base de datos. 🎉
