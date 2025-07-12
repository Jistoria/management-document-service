# Seeders Documentation

Este proyecto incluye dos tipos de seeders diseñados para diferentes entornos y propósitos.

## ✅ Estado: Completamente Funcional

Ambos seeders han sido probados y funcionan correctamente con PostgreSQL. Incluyen:

-   Compatibilidad completa con PostgreSQL (no MySQL)
-   Manejo inteligente de duplicados con `firstOrCreate()`
-   Validación de campos según modelos existentes
-   Comando personalizado `seed:data` para facilitar el uso

## 📋 Tipos de Seeders

### 1. ProductionSeeder

**Propósito**: Datos base esenciales para producción
**Ubicación**: `database/seeders/ProductionSeeder.php`

#### Características:

-   ✅ Datos mínimos y esenciales
-   ✅ Estructura organizacional básica realista
-   ✅ Tipos de documento estándar
-   ✅ Roles académicos estándar
-   ✅ Esquemas de metadatos base
-   ✅ Subsistemas principales
-   ✅ Datos que se mantendrán en producción

#### Datos que crea:

-   **1 Sede Principal** con 4 facultades
-   **20 Carreras** distribuidas en las facultades
-   **6 Subsistemas** estándar
-   **21 Tipos de documento** comunes
-   **18 Roles académicos** estándar
-   **3 Esquemas de metadatos** base
-   **4 Tipos de unidades de almacenamiento**
-   **Estructura inicial de procesos**

### 2. TestingSeeder

**Propósito**: Datos de prueba con factories para testing/desarrollo
**Ubicación**: `database/seeders/TestingSeeder.php`

#### Características:

-   ✅ Usa factories para generar datos aleatorios
-   ✅ Crea gran cantidad de datos variados
-   ✅ Incluye escenarios específicos de prueba
-   ✅ Limpia datos existentes antes de crear nuevos
-   ✅ Ideal para desarrollo y testing

#### Datos que crea:

-   **4+ Sedes** con nombres aleatorios
-   **12+ Departamentos** con relaciones
-   **60+ Carreras** distribuidas aleatoriamente
-   **Escenarios específicos** para testing
-   **Datos de soporte** completos

## 🚀 Uso de los Seeders

### Comando Personalizado (Recomendado)

El comando `seed:data` facilita el uso de los seeders:

```bash
# Ejecutar seeder de producción (por defecto)
php artisan seed:data

# Ejecutar seeder de producción específicamente
php artisan seed:data production

# Ejecutar seeder de testing
php artisan seed:data testing

# Ejecutar ambos seeders
php artisan seed:data both

# Refrescar base de datos y ejecutar seeder
php artisan seed:data production --fresh

# Forzar ejecución en producción (testing)
php artisan seed:data testing --force
```

### Comandos Artisan Tradicionales

### Ejecutar seeder por defecto (automático según entorno)

```bash
php artisan db:seed
```

### Ejecutar seeder de producción específicamente

```bash
php artisan db:seed --class=ProductionSeeder
```

### Ejecutar seeder de testing específicamente

```bash
php artisan db:seed --class=TestingSeeder
```

### Refrescar base de datos y ejecutar seeders

```bash
php artisan migrate:refresh --seed
```

### Ejecutar seeder específico con refresh

```bash
php artisan migrate:refresh --seed --class=ProductionSeeder
```

## 🏭 Entornos

### Producción

-   Por defecto ejecuta `ProductionSeeder`
-   Crea datos esenciales y seguros
-   No sobrescribe datos existentes

### Desarrollo/Testing

-   Por defecto ejecuta `ProductionSeeder` (datos base)
-   Recomendado usar `TestingSeeder` para pruebas
-   `TestingSeeder` limpia datos existentes

## 📁 Estructura de Datos Creados

### Jerarquía Organizacional (ProductionSeeder)

```
Sede Principal
├── Facultad de Ingeniería y Tecnología
│   ├── Ingeniería de Sistemas
│   ├── Ingeniería Civil
│   ├── Ingeniería Industrial
│   ├── Ingeniería Electrónica
│   └── Ingeniería de Software
├── Facultad de Ciencias
│   ├── Licenciatura en Matemáticas
│   ├── Licenciatura en Física
│   ├── Licenciatura en Química
│   └── Licenciatura en Biología
├── Facultad de Ciencias de la Salud
│   ├── Medicina
│   ├── Enfermería
│   ├── Fisioterapia
│   └── Psicología
└── Facultad de Ciencias Económicas y Administrativas
    ├── Administración de Empresas
    ├── Contaduría Pública
    ├── Economía
    └── Mercadeo
```

### Subsistemas Principales

-   **SGA**: Sistema de Gestión Académica
-   **BIBLIOTECA**: Sistema de Biblioteca Digital
-   **LABORATORIOS**: Sistema de Laboratorios
-   **INVESTIGACION**: Sistema de Proyectos de Investigación
-   **PRACTICAS**: Sistema de Prácticas Profesionales
-   **GRADUACION**: Sistema de Graduación

### Tipos de Documento Estándar

-   Documentos académicos (Acta de Grado, Certificados, etc.)
-   Documentos de investigación (Proyectos, Tesis, etc.)
-   Documentos administrativos (Resoluciones, Memorandos, etc.)
-   Documentos de laboratorio (Protocolos, Informes, etc.)
-   Documentos de prácticas (Convenios, Evaluaciones, etc.)

### Roles Académicos

-   Roles estudiantiles (Pregrado, Posgrado, Intercambio)
-   Roles docentes (Catedrático, Asociado, Asistente, etc.)
-   Roles administrativos (Decano, Director, Coordinador, etc.)
-   Roles de investigación (Investigador Principal, Asociado, etc.)
-   Roles especializados (Laboratorio, Biblioteca)

## 🔧 Factories Disponibles

Las factories están disponibles para:

-   `HeadOfficeFactory`: Genera sedes con nombres realistas
-   `DepartmentFactory`: Genera departamentos/facultades
-   `CareerFactory`: Genera carreras académicas

### Uso de Factories en Testing

```php
// Crear una sede con departamentos y carreras
HeadOffice::factory()
    ->has(Department::factory()->count(3)
        ->has(Career::factory()->count(5))
    )
    ->create();

// Crear una sede específica
HeadOffice::factory()->mainCampus()->create();

// Crear departamento de ingeniería
Department::factory()->engineering()->create();
```

## ⚠️ Consideraciones Importantes

### Para Producción

-   Ejecutar `ProductionSeeder` una sola vez
-   No elimina datos existentes
-   Datos seguros y esenciales
-   Verificar integridad después de ejecutar

### Para Testing

-   `TestingSeeder` elimina datos existentes
-   **NO usar en producción**
-   Genera grandes volúmenes de datos
-   Ideal para pruebas de rendimiento

### Para Desarrollo

-   Usar `ProductionSeeder` para datos base
-   Usar `TestingSeeder` para pruebas específicas
-   Combinar con factories para casos particulares

## 🔄 Flujo Recomendado

### Configuración Inicial (Producción)

1. `php artisan migrate`
2. `php artisan seed:data production`

### Desarrollo

1. `php artisan seed:data production --fresh` (datos base limpios)
2. `php artisan seed:data testing` (datos de prueba adicionales, opcional)

### Testing Automatizado

```php
// En tests
public function setUp(): void
{
    parent::setUp();
    $this->artisan('migrate:refresh');
    $this->artisan('db:seed', ['--class' => 'TestingSeeder']);
}
```

## 📋 Ejemplos de Uso

### Configuración inicial de desarrollo

```bash
# Configura la base de datos desde cero con datos de producción
php artisan seed:data production --fresh
```

### Preparar datos para testing

```bash
# Limpia y crea datos de testing
php artisan seed:data testing --fresh
```

### Agregar datos de testing a datos existentes

```bash
# Agrega datos de testing sin limpiar
php artisan seed:data testing
```

### Configuración completa (desarrollo avanzado)

```bash
# Crea datos base y de testing
php artisan seed:data both --fresh
```

### Verificar qué comando usar

```bash
# Ver ayuda del comando personalizado
php artisan help seed:data

# Ver todos los comandos de seeding disponibles
php artisan list | grep seed
```
