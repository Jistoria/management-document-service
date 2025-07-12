# 🗄️ Database Management - Management Document Service

## 📋 Descripción

Este setup automatiza la gestión completa de la base de datos PostgreSQL para el Management Document Service, incluyendo inicialización automática, verificación de sincronización y utilidades de mantenimiento.

## 🚀 Inicio Rápido

### 1. Iniciar el Sistema

```bash
# Iniciar con Docker Compose
docker-compose up -d

# O usar el script de utilidades
./manage-db.sh start
```

### 2. Verificar Estado

```bash
# Ver estado del contenedor
./manage-db.sh status

# Verificar sincronización de la DB
./manage-db.sh check
```

## 🏗️ Arquitectura del Sistema

### 📁 Estructura de Archivos

```
management-document-service/
├── docker-compose.yml                 # Configuración principal
├── recreate_database_complete.sql     # Script completo de DB
├── manage-db.sh                      # Utilidades de gestión
├── docker/
│   ├── init-db.sh                    # Script de inicialización
│   └── db_sync_check.sql             # Verificaciones de sincronización
└── README_DATABASE.md               # Esta documentación
```

### 🔄 Flujo de Inicialización

1. **Container Start**: Se inicia el contenedor PostgreSQL
2. **Health Check**: Se verifica que PostgreSQL esté listo
3. **Database Check**: Se verifica si la DB existe y está sincronizada
4. **Auto-Initialize**: Si no existe o falta algo, se ejecuta recreación completa
5. **Sync Verification**: Se valida que todo esté correcto

## 📊 Docker Compose

### 🐳 Servicios

#### `postgres`

-   **Imagen**: `postgres:16`
-   **Puerto**: `5450:5432`
-   **Volumen**: Persistente para datos
-   **Health Check**: Verificación automática de estado
-   **Init Scripts**: Carga automática de scripts de inicialización

#### `db-sync-checker` (Debug)

-   **Perfil**: `debug` (opcional)
-   **Función**: Verificación detallada de sincronización
-   **Uso**: `docker-compose --profile debug up`

### 🔧 Configuración

```yaml
environment:
    POSTGRES_DB: management_db
    POSTGRES_USER: user123
    POSTGRES_PASSWORD: pass123
    POSTGRES_INITDB_ARGS: "--encoding=UTF8 --locale=C"
```

## 🛠️ Utilidades de Gestión

### 📜 Script `manage-db.sh`

Script completo para gestión de la base de datos:

```bash
# Comandos básicos
./manage-db.sh start          # Iniciar DB
./manage-db.sh stop           # Detener DB
./manage-db.sh restart        # Reiniciar DB
./manage-db.sh status         # Ver estado
./manage-db.sh logs           # Ver logs

# Gestión de datos
./manage-db.sh connect        # Conectar con psql
./manage-db.sh check          # Verificar sincronización
./manage-db.sh recreate       # Recrear DB desde cero

# Backup y restore
./manage-db.sh backup         # Crear backup
./manage-db.sh restore file   # Restaurar backup
./manage-db.sh reset          # Reset completo

# Debug
./manage-db.sh debug          # Verificación detallada
```

## 🔍 Scripts de Inicialización

### 📋 `init-db.sh`

Script principal que se ejecuta automáticamente:

-   ✅ **Detección inteligente**: Verifica si la DB existe
-   ✅ **Recreación automática**: Si no existe o está incompleta
-   ✅ **Verificación post-setup**: Valida que todo esté correcto
-   ✅ **Logs detallados**: Output colorizado para debugging

### 📊 `db_sync_check.sql`

Script de verificación completa:

-   ✅ **Tablas**: Verifica todas las tablas esperadas
-   ✅ **Funciones**: Valida funciones del sistema
-   ✅ **Vistas**: Comprueba vistas y vistas materializadas
-   ✅ **Índices**: Verifica índices importantes
-   ✅ **Constraints**: Valida integridad referencial
-   ✅ **Datos iniciales**: Comprueba datos del sistema

## 📈 Casos de Uso

### 🆕 Primera Instalación

```bash
# 1. Clonar repositorio
git clone <repo>
cd management-document-service

# 2. Iniciar sistema
./manage-db.sh start

# 3. Verificar instalación
./manage-db.sh check
```

### 🔄 Desarrollo Diario

```bash
# Iniciar entorno de desarrollo
./manage-db.sh start

# Conectar para consultas
./manage-db.sh connect

# Ver logs si hay problemas
./manage-db.sh logs
```

### 🐛 Debugging y Mantenimiento

```bash
# Verificación detallada
./manage-db.sh debug

# Si hay problemas, recrear
./manage-db.sh recreate

# Reset completo en caso extremo
./manage-db.sh reset
```

### 💾 Backup y Restore

```bash
# Crear backup antes de cambios importantes
./manage-db.sh backup

# Restaurar si algo sale mal
./manage-db.sh restore backup_2025-07-12_14-30-00.sql
```

## 🔧 Configuración Avanzada

### ⚙️ Variables de Entorno

Puedes personalizar la configuración modificando `docker-compose.yml`:

```yaml
environment:
    POSTGRES_DB: tu_base_de_datos
    POSTGRES_USER: tu_usuario
    POSTGRES_PASSWORD: tu_password
```

### 🚪 Puertos

Por defecto usa el puerto `5450`. Para cambiarlo:

```yaml
ports:
    - "TU_PUERTO:5432"
```

### 💿 Volúmenes

Los datos se persisten en el volumen `postgres_data`. Para usar un directorio local:

```yaml
volumes:
    - ./data:/var/lib/postgresql/data
```

## 🆘 Solución de Problemas

### ❌ Container no inicia

```bash
# Ver logs detallados
./manage-db.sh logs

# Verificar Docker
docker info

# Reset completo
./manage-db.sh reset
```

### 🔍 DB no sincronizada

```bash
# Verificar estado
./manage-db.sh check

# Verificación detallada
./manage-db.sh debug

# Recrear si es necesario
./manage-db.sh recreate
```

### 🐌 Performance lenta

```bash
# Conectar y verificar
./manage-db.sh connect

# En psql, verificar índices
\di

# Verificar estadísticas
SELECT * FROM pg_stat_user_tables;
```

## 📋 Checklist de Verificación

### ✅ Sistema Saludable

-   [ ] Container corriendo: `./manage-db.sh status`
-   [ ] Health check verde: `docker-compose ps`
-   [ ] DB sincronizada: `./manage-db.sh check`
-   [ ] Datos iniciales presentes
-   [ ] Conexión funcional: `./manage-db.sh connect`

### ✅ Tablas Principales

-   [ ] `head_offices`, `departments`, `careers`
-   [ ] `metadata_schemas`, `metadata_fields`
-   [ ] `audit_logs`, `audit_metrics`
-   [ ] `external_apis`, `academic_roles`
-   [ ] Vistas: `v_audit_summary_by_user`, `v_recent_changes`
-   [ ] Vista materializada: `mv_process_hierarchy`

### ✅ Datos Iniciales

-   [ ] 4+ External APIs configuradas
-   [ ] 7+ Academic Roles del sistema
-   [ ] 2+ Metadata Schemas base

## 🔗 Enlaces Útiles

-   [PostgreSQL 16 Documentation](https://www.postgresql.org/docs/16/)
-   [Docker Compose Reference](https://docs.docker.com/compose/)
-   [psql Commands](https://www.postgresql.org/docs/current/app-psql.html)

## 📞 Soporte

Para problemas o mejoras:

1. Verificar logs: `./manage-db.sh logs`
2. Ejecutar debug: `./manage-db.sh debug`
3. Revisar esta documentación
4. Crear backup antes de cambios: `./manage-db.sh backup`

---

**Management Document Service Database Setup v4.0**  
_Automatización completa de base de datos PostgreSQL_ 🚀
