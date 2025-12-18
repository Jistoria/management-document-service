#!/bin/bash
# =====================================================================================
# UTILIDADES DE GESTIÓN DE BASE DE DATOS - MANAGEMENT DOCUMENT SERVICE
# =====================================================================================
# Descripción: Script con comandos útiles para gestionar la base de datos
# Uso: ./manage-db.sh [comando]
# =====================================================================================

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Variables de configuración
DB_CONTAINER="postgres-db"
DB_NAME="management_db"
DB_USER="user123"
DB_PASSWORD="pass123"
DB_PORT="5450"

# Función para mostrar ayuda
show_help() {
    echo -e "${BLUE}=================================================${NC}"
    echo -e "${BLUE} MANAGEMENT DOCUMENT SERVICE - DB UTILITIES${NC}"
    echo -e "${BLUE}=================================================${NC}"
    echo ""
    echo -e "${CYAN}Comandos disponibles:${NC}"
    echo ""
    echo -e "  ${GREEN}start${NC}           - Iniciar contenedor PostgreSQL"
    echo -e "  ${GREEN}stop${NC}            - Detener contenedor PostgreSQL"
    echo -e "  ${GREEN}restart${NC}         - Reiniciar contenedor PostgreSQL"
    echo -e "  ${GREEN}status${NC}          - Ver estado del contenedor"
    echo -e "  ${GREEN}logs${NC}            - Ver logs del contenedor"
    echo -e "  ${GREEN}connect${NC}         - Conectar a la base de datos (psql)"
    echo -e "  ${GREEN}check${NC}           - Verificar sincronización de la DB"
    echo -e "  ${GREEN}recreate${NC}        - Recrear la base de datos desde cero"
    echo -e "  ${GREEN}backup${NC}          - Crear backup de la base de datos"
    echo -e "  ${GREEN}restore [file]${NC}  - Restaurar backup de la base de datos"
    echo -e "  ${GREEN}reset${NC}           - Eliminar volumen y recrear todo"
    echo -e "  ${GREEN}debug${NC}           - Ejecutar verificación detallada"
    echo ""
    echo -e "${YELLOW}Ejemplos:${NC}"
    echo -e "  ${CYAN}./manage-db.sh start${NC}"
    echo -e "  ${CYAN}./manage-db.sh check${NC}"
    echo -e "  ${CYAN}./manage-db.sh backup${NC}"
    echo -e "  ${CYAN}./manage-db.sh restore backup_2025-07-12.sql${NC}"
    echo ""
}

# Función para verificar si Docker está corriendo
check_docker() {
    if ! docker info >/dev/null 2>&1; then
        echo -e "${RED}❌ Docker no está corriendo${NC}"
        exit 1
    fi
}

# Función para iniciar el contenedor
start_db() {
    echo -e "${BLUE}🚀 Iniciando contenedor PostgreSQL...${NC}"
    docker-compose up -d postgres
    echo -e "${GREEN} Contenedor iniciado${NC}"

    # Esperar a que esté saludable
    echo -e "${YELLOW}⏳ Esperando a que la DB esté lista...${NC}"
    timeout 60 bash -c 'until docker-compose ps postgres | grep -q "healthy"; do sleep 2; done'
    echo -e "${GREEN} Base de datos lista${NC}"
}

# Función para detener el contenedor
stop_db() {
    echo -e "${YELLOW}🛑 Deteniendo contenedor PostgreSQL...${NC}"
    docker-compose stop postgres
    echo -e "${GREEN} Contenedor detenido${NC}"
}

# Función para reiniciar el contenedor
restart_db() {
    echo -e "${BLUE}🔄 Reiniciando contenedor PostgreSQL...${NC}"
    stop_db
    start_db
}

# Función para ver el estado
check_status() {
    echo -e "${BLUE}📊 Estado del contenedor:${NC}"
    docker-compose ps postgres
    echo ""
    echo -e "${BLUE}📊 Logs recientes:${NC}"
    docker-compose logs --tail=10 postgres
}

# Función para ver logs
show_logs() {
    echo -e "${BLUE}📜 Logs del contenedor PostgreSQL:${NC}"
    docker-compose logs -f postgres
}

# Función para conectar a la DB
connect_db() {
    echo -e "${BLUE}🔗 Conectando a la base de datos...${NC}"
    echo -e "${YELLOW}💡 Usa \\q para salir${NC}"
    docker exec -it $DB_CONTAINER psql -U $DB_USER -d $DB_NAME
}

# Función para verificar sincronización
check_sync() {
    echo -e "${BLUE}🔍 Verificando sincronización de la base de datos...${NC}"
    docker exec -i $DB_CONTAINER psql -U $DB_USER -d $DB_NAME -f /docker-entrypoint-initdb.d/db_sync_check.sql
}

# Función para recrear la base de datos
recreate_db() {
    echo -e "${RED}⚠️  ADVERTENCIA: Esto eliminará todos los datos existentes${NC}"
    read -p "¿Estás seguro? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${BLUE}🔨 Recreando base de datos...${NC}"

        # Eliminar la base de datos actual
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d postgres -c "DROP DATABASE IF EXISTS $DB_NAME;"

        # Crear nueva base de datos
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d postgres -c "CREATE DATABASE $DB_NAME;"

        # Ejecutar script de recreación
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d $DB_NAME -f /docker-entrypoint-initdb.d/recreate_database_complete.sql

        echo -e "${GREEN} Base de datos recreada exitosamente${NC}"
        check_sync
    else
        echo -e "${YELLOW}❌ Operación cancelada${NC}"
    fi
}

# Función para crear backup
backup_db() {
    local backup_file="backup_$(date +%Y-%m-%d_%H-%M-%S).sql"
    echo -e "${BLUE}💾 Creando backup: $backup_file${NC}"

    docker exec $DB_CONTAINER pg_dump -U $DB_USER -d $DB_NAME > "$backup_file"

    echo -e "${GREEN} Backup creado: $backup_file${NC}"
    echo -e "${CYAN}📁 Tamaño: $(du -h "$backup_file" | cut -f1)${NC}"
}

# Función para restaurar backup
restore_db() {
    local backup_file="$1"

    if [ -z "$backup_file" ]; then
        echo -e "${RED}❌ Especifica el archivo de backup${NC}"
        echo -e "${YELLOW}Uso: ./manage-db.sh restore backup_file.sql${NC}"
        exit 1
    fi

    if [ ! -f "$backup_file" ]; then
        echo -e "${RED}❌ Archivo no encontrado: $backup_file${NC}"
        exit 1
    fi

    echo -e "${RED}⚠️  ADVERTENCIA: Esto eliminará todos los datos existentes${NC}"
    read -p "¿Restaurar desde $backup_file? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${BLUE}📥 Restaurando backup: $backup_file${NC}"

        # Eliminar y recrear base de datos
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d postgres -c "DROP DATABASE IF EXISTS $DB_NAME;"
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d postgres -c "CREATE DATABASE $DB_NAME;"

        # Restaurar backup
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d $DB_NAME < "$backup_file"

        echo -e "${GREEN} Backup restaurado exitosamente${NC}"
        check_sync
    else
        echo -e "${YELLOW}❌ Operación cancelada${NC}"
    fi
}

# Función para reset completo
reset_db() {
    echo -e "${RED}⚠️  ADVERTENCIA: Esto eliminará TODO (contenedor, volumen, datos)${NC}"
    read -p "¿Estás COMPLETAMENTE seguro? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${BLUE}🗑️  Eliminando todo...${NC}"

        # Detener y eliminar contenedor
        docker-compose down postgres

        # Eliminar volumen
        docker volume rm management-document-service_postgres_data 2>/dev/null || true

        # Reiniciar
        echo -e "${BLUE}🚀 Iniciando desde cero...${NC}"
        start_db

        echo -e "${GREEN} Reset completo realizado${NC}"
    else
        echo -e "${YELLOW}❌ Operación cancelada${NC}"
    fi
}

# Función para debug detallado
debug_db() {
    echo -e "${BLUE}🐛 Ejecutando verificación detallada...${NC}"
    docker-compose --profile debug up db-sync-checker
}

# Función principal
main() {
    check_docker

    case "${1:-}" in
        start)
            start_db
            ;;
        stop)
            stop_db
            ;;
        restart)
            restart_db
            ;;
        status)
            check_status
            ;;
        logs)
            show_logs
            ;;
        connect)
            connect_db
            ;;
        check)
            check_sync
            ;;
        recreate)
            recreate_db
            ;;
        backup)
            backup_db
            ;;
        restore)
            restore_db "$2"
            ;;
        reset)
            reset_db
            ;;
        debug)
            debug_db
            ;;
        help|--help|-h)
            show_help
            ;;
        "")
            show_help
            ;;
        *)
            echo -e "${RED}❌ Comando desconocido: $1${NC}"
            echo ""
            show_help
            exit 1
            ;;
    esac
}

# Ejecutar función principal
main "$@"
