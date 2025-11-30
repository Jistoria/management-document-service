#!/bin/bash
# =====================================================================================
# SCRIPT DE INICIALIZACIÓN DE BASE DE DATOS - MANAGEMENT DOCUMENT SERVICE
# =====================================================================================
# Descripción: Script que se ejecuta al iniciar el contenedor PostgreSQL
# Funcionalidad: Crea la base de datos completa si no existe o verifica sincronización
# =====================================================================================

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=================================================${NC}"
echo -e "${BLUE} MANAGEMENT DOCUMENT SERVICE - DB INITIALIZATION${NC}"
echo -e "${BLUE}=================================================${NC}"

# Variables de configuración
DB_NAME="management_db"
DB_USER="user123"
DB_HOST="127.0.0.1"
SQL_SCRIPT="/docker-entrypoint-initdb.d/recreate_database_complete.sql"
SYNC_CHECK_SCRIPT="/docker-entrypoint-initdb.d/db_sync_check.sql"

# Función para verificar si la base de datos existe y tiene las tablas
check_database_exists() {
    echo -e "${YELLOW}🔍 Verificando estado de la base de datos...${NC}"

    # Verificar si existen las tablas principales
    TABLE_COUNT=$(psql -U "$DB_USER" -d "$DB_NAME" -t -c "
        SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_schema = 'public'
        AND table_name IN (
            'head_offices', 'departments', 'careers', 'subsystems',
            'metadata_schemas', 'metadata_fields', 'audit_logs',
            'external_apis', 'document_types', 'academic_roles'
        );" 2>/dev/null | xargs)

    if [ "$TABLE_COUNT" -eq "10" ]; then
        echo -e "${GREEN}✅ Base de datos existe con todas las tablas principales${NC}"
        return 0
    else
        echo -e "${YELLOW}⚠️  Base de datos incompleta o no existe (encontradas $TABLE_COUNT/10 tablas)${NC}"
        return 1
    fi
}

# Función para verificar sincronización de la base de datos
verify_database_sync() {
    echo -e "${YELLOW}🔄 Verificando sincronización de la base de datos...${NC}"

    # Verificar que todas las tablas esperadas existen
    EXPECTED_TABLES=("head_offices" "departments" "careers" "subsystems" "subsystem_entity_links"
                     "subsystem_group_links" "subsystem_groups"
                     "process_categories" "processes" "document_types" "academic_roles"
                     "required_documents" "storage_unit_types" "storage_units"
                     "metadata_schemas" "metadata_fields" "metadata_schema_events"
                     "audit_logs" "audit_metrics" "external_apis")

    MISSING_TABLES=()

    for table in "${EXPECTED_TABLES[@]}"; do
        EXISTS=$(psql -U "$DB_USER" -d "$DB_NAME" -t -c "
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = 'public' AND table_name = '$table';" 2>/dev/null | xargs)

        if [ "$EXISTS" -eq "0" ]; then
            MISSING_TABLES+=("$table")
        fi
    done

    if [ ${#MISSING_TABLES[@]} -eq 0 ]; then
        echo -e "${GREEN}✅ Todas las tablas están presentes${NC}"
        echo -e "${GREEN}✅ Estructura de base de datos sincronizada${NC}"
        echo -e "${BLUE}ℹ️  Los datos iniciales serán cargados por Laravel Seeders${NC}"
        return 0
    else
        echo -e "${RED}❌ Faltan tablas: ${MISSING_TABLES[*]}${NC}"
        return 1
    fi
}

# Función para ejecutar el script de recreación completa
recreate_database() {
    echo -e "${BLUE}🚀 Ejecutando recreación completa de la base de datos...${NC}"

    if [ -f "$SQL_SCRIPT" ]; then
        echo -e "${YELLOW}📄 Ejecutando: $SQL_SCRIPT${NC}"

        # Ejecutar el script de recreación
        if psql -U "$DB_USER" -d "$DB_NAME" -f "$SQL_SCRIPT"; then
            echo -e "${GREEN}✅ Base de datos creada exitosamente${NC}"

            # Verificar que todo se creó correctamente
            if verify_database_sync; then
                echo -e "${GREEN}🎉 Inicialización completa y verificada${NC}"
                return 0
            else
                echo -e "${RED}❌ Error en la verificación post-creación${NC}"
                return 1
            fi
        else
            echo -e "${RED}❌ Error ejecutando el script de recreación${NC}"
            return 1
        fi
    else
        echo -e "${RED}❌ Script de recreación no encontrado: $SQL_SCRIPT${NC}"
        return 1
    fi
}

# Función principal
main() {
    echo -e "${BLUE}🏁 Iniciando proceso de inicialización...${NC}"

    # En el contexto de docker-entrypoint-initdb.d, PostgreSQL ya está listo
    echo -e "${GREEN}✅ PostgreSQL está listo${NC}"

    # Verificar estado actual de la base de datos
    if check_database_exists; then
        # La base de datos existe, verificar sincronización
        if verify_database_sync; then
            echo -e "${GREEN}🎯 Base de datos ya está sincronizada, no se requiere acción${NC}"
            exit 0
        else
            echo -e "${YELLOW}🔧 Base de datos necesita sincronización, recreando...${NC}"
            recreate_database
        fi
    else
        # La base de datos no existe o está incompleta, crear desde cero
        echo -e "${BLUE}🆕 Creando base de datos desde cero...${NC}"
        recreate_database
    fi

    echo -e "${BLUE}=================================================${NC}"
    echo -e "${BLUE} INICIALIZACIÓN COMPLETADA${NC}"
    echo -e "${BLUE}=================================================${NC}"
}

# Ejecutar función principal
main "$@"
