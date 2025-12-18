# =====================================================================================
# UTILIDADES DE GESTIÓN DE BASE DE DATOS - MANAGEMENT DOCUMENT SERVICE (Windows)
# =====================================================================================
# Descripción: Script PowerShell con comandos útiles para gestionar la base de datos
# Uso: .\manage-db.ps1 [comando]
# =====================================================================================

param(
    [Parameter(Position=0)]
    [string]$Command,

    [Parameter(Position=1)]
    [string]$BackupFile
)

# Variables de configuración
$DB_CONTAINER = "postgres-db"
$DB_NAME = "management_db"
$DB_USER = "user123"
$DB_PASSWORD = "pass123"
$DB_PORT = "5450"

# Función para mostrar colores en PowerShell
function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )

    $colors = @{
        "Red" = "Red"
        "Green" = "Green"
        "Yellow" = "Yellow"
        "Blue" = "Blue"
        "Cyan" = "Cyan"
        "White" = "White"
    }

    Write-Host $Message -ForegroundColor $colors[$Color]
}

# Función para mostrar ayuda
function Show-Help {
    Write-ColorOutput "=================================================" "Blue"
    Write-ColorOutput " MANAGEMENT DOCUMENT SERVICE - DB UTILITIES" "Blue"
    Write-ColorOutput "=================================================" "Blue"
    Write-Host ""
    Write-ColorOutput "Comandos disponibles:" "Cyan"
    Write-Host ""
    Write-ColorOutput "  start           - Iniciar contenedor PostgreSQL" "Green"
    Write-ColorOutput "  stop            - Detener contenedor PostgreSQL" "Green"
    Write-ColorOutput "  restart         - Reiniciar contenedor PostgreSQL" "Green"
    Write-ColorOutput "  status          - Ver estado del contenedor" "Green"
    Write-ColorOutput "  logs            - Ver logs del contenedor" "Green"
    Write-ColorOutput "  connect         - Conectar a la base de datos (psql)" "Green"
    Write-ColorOutput "  check           - Verificar sincronización de la DB" "Green"
    Write-ColorOutput "  recreate        - Recrear la base de datos desde cero" "Green"
    Write-ColorOutput "  backup          - Crear backup de la base de datos" "Green"
    Write-ColorOutput "  restore [file]  - Restaurar backup de la base de datos" "Green"
    Write-ColorOutput "  reset           - Eliminar volumen y recrear todo" "Green"
    Write-ColorOutput "  debug           - Ejecutar verificación detallada" "Green"
    Write-Host ""
    Write-ColorOutput "Ejemplos:" "Yellow"
    Write-ColorOutput "  .\manage-db.ps1 start" "Cyan"
    Write-ColorOutput "  .\manage-db.ps1 check" "Cyan"
    Write-ColorOutput "  .\manage-db.ps1 backup" "Cyan"
    Write-ColorOutput "  .\manage-db.ps1 restore backup_2025-07-12.sql" "Cyan"
    Write-Host ""
}

# Función para verificar si Docker está corriendo
function Test-Docker {
    try {
        docker info | Out-Null
        return $true
    }
    catch {
        Write-ColorOutput "❌ Docker no está corriendo" "Red"
        exit 1
    }
}

# Función para iniciar el contenedor
function Start-Database {
    Write-ColorOutput "🚀 Iniciando contenedor PostgreSQL..." "Blue"
    docker-compose up -d postgres
    Write-ColorOutput " Contenedor iniciado" "Green"

    # Esperar a que esté saludable
    Write-ColorOutput "⏳ Esperando a que la DB esté lista..." "Yellow"
    $timeout = 60
    $elapsed = 0
    do {
        Start-Sleep -Seconds 2
        $elapsed += 2
        $status = docker-compose ps postgres | Select-String "healthy"
    } while (-not $status -and $elapsed -lt $timeout)

    if ($status) {
        Write-ColorOutput " Base de datos lista" "Green"
    } else {
        Write-ColorOutput "⚠️ Timeout esperando la base de datos" "Yellow"
    }
}

# Función para detener el contenedor
function Stop-Database {
    Write-ColorOutput "🛑 Deteniendo contenedor PostgreSQL..." "Yellow"
    docker-compose stop postgres
    Write-ColorOutput " Contenedor detenido" "Green"
}

# Función para reiniciar el contenedor
function Restart-Database {
    Write-ColorOutput "🔄 Reiniciando contenedor PostgreSQL..." "Blue"
    Stop-Database
    Start-Database
}

# Función para ver el estado
function Get-Status {
    Write-ColorOutput "📊 Estado del contenedor:" "Blue"
    docker-compose ps postgres
    Write-Host ""
    Write-ColorOutput "📊 Logs recientes:" "Blue"
    docker-compose logs --tail=10 postgres
}

# Función para ver logs
function Show-Logs {
    Write-ColorOutput "📜 Logs del contenedor PostgreSQL:" "Blue"
    docker-compose logs -f postgres
}

# Función para conectar a la DB
function Connect-Database {
    Write-ColorOutput "🔗 Conectando a la base de datos..." "Blue"
    Write-ColorOutput "💡 Usa \q para salir" "Yellow"
    docker exec -it $DB_CONTAINER psql -U $DB_USER -d $DB_NAME
}

# Función para verificar sincronización
function Test-Sync {
    Write-ColorOutput "🔍 Verificando sincronización de la base de datos..." "Blue"
    docker exec -i $DB_CONTAINER psql -U $DB_USER -d $DB_NAME -f /docker-entrypoint-initdb.d/db_sync_check.sql
}

# Función para recrear la base de datos
function Reset-Database {
    Write-ColorOutput "⚠️  ADVERTENCIA: Esto eliminará todos los datos existentes" "Red"
    $confirm = Read-Host "¿Estás seguro? (y/N)"
    if ($confirm -eq "y" -or $confirm -eq "Y") {
        Write-ColorOutput "🔨 Recreando base de datos..." "Blue"

        # Eliminar la base de datos actual
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d postgres -c "DROP DATABASE IF EXISTS $DB_NAME;"

        # Crear nueva base de datos
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d postgres -c "CREATE DATABASE $DB_NAME;"

        # Ejecutar script de recreación
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d $DB_NAME -f /docker-entrypoint-initdb.d/recreate_database_complete.sql

        Write-ColorOutput " Base de datos recreada exitosamente" "Green"
        Test-Sync
    } else {
        Write-ColorOutput "❌ Operación cancelada" "Yellow"
    }
}

# Función para crear backup
function New-Backup {
    $timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
    $backupFile = "backup_$timestamp.sql"
    Write-ColorOutput "💾 Creando backup: $backupFile" "Blue"

    docker exec $DB_CONTAINER pg_dump -U $DB_USER -d $DB_NAME | Out-File -FilePath $backupFile -Encoding UTF8

    Write-ColorOutput " Backup creado: $backupFile" "Green"
    $size = (Get-Item $backupFile).Length / 1MB
    Write-ColorOutput "📁 Tamaño: $([math]::Round($size, 2)) MB" "Cyan"
}

# Función para restaurar backup
function Restore-Backup {
    param([string]$BackupFile)

    if (-not $BackupFile) {
        Write-ColorOutput "❌ Especifica el archivo de backup" "Red"
        Write-ColorOutput "Uso: .\manage-db.ps1 restore backup_file.sql" "Yellow"
        return
    }

    if (-not (Test-Path $BackupFile)) {
        Write-ColorOutput "❌ Archivo no encontrado: $BackupFile" "Red"
        return
    }

    Write-ColorOutput "⚠️  ADVERTENCIA: Esto eliminará todos los datos existentes" "Red"
    $confirm = Read-Host "¿Restaurar desde $BackupFile? (y/N)"
    if ($confirm -eq "y" -or $confirm -eq "Y") {
        Write-ColorOutput "📥 Restaurando backup: $BackupFile" "Blue"

        # Eliminar y recrear base de datos
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d postgres -c "DROP DATABASE IF EXISTS $DB_NAME;"
        docker exec -i $DB_CONTAINER psql -U $DB_USER -d postgres -c "CREATE DATABASE $DB_NAME;"

        # Restaurar backup
        Get-Content $BackupFile | docker exec -i $DB_CONTAINER psql -U $DB_USER -d $DB_NAME

        Write-ColorOutput " Backup restaurado exitosamente" "Green"
        Test-Sync
    } else {
        Write-ColorOutput "❌ Operación cancelada" "Yellow"
    }
}

# Función para reset completo
function Reset-Complete {
    Write-ColorOutput "⚠️  ADVERTENCIA: Esto eliminará TODO (contenedor, volumen, datos)" "Red"
    $confirm = Read-Host "¿Estás COMPLETAMENTE seguro? (y/N)"
    if ($confirm -eq "y" -or $confirm -eq "Y") {
        Write-ColorOutput "🗑️  Eliminando todo..." "Blue"

        # Detener y eliminar contenedor
        docker-compose down postgres

        # Eliminar volumen
        try {
            docker volume rm management-document-service_postgres_data
        } catch {
            # Ignorar error si el volumen no existe
        }

        # Reiniciar
        Write-ColorOutput "🚀 Iniciando desde cero..." "Blue"
        Start-Database

        Write-ColorOutput " Reset completo realizado" "Green"
    } else {
        Write-ColorOutput "❌ Operación cancelada" "Yellow"
    }
}

# Función para debug detallado
function Start-Debug {
    Write-ColorOutput "🐛 Ejecutando verificación detallada..." "Blue"
    docker-compose --profile debug up db-sync-checker
}

# Función principal
function Main {
    Test-Docker

    switch ($Command.ToLower()) {
        "start" { Start-Database }
        "stop" { Stop-Database }
        "restart" { Restart-Database }
        "status" { Get-Status }
        "logs" { Show-Logs }
        "connect" { Connect-Database }
        "check" { Test-Sync }
        "recreate" { Reset-Database }
        "backup" { New-Backup }
        "restore" { Restore-Backup -BackupFile $BackupFile }
        "reset" { Reset-Complete }
        "debug" { Start-Debug }
        "help" { Show-Help }
        "" { Show-Help }
        default {
            Write-ColorOutput "❌ Comando desconocido: $Command" "Red"
            Write-Host ""
            Show-Help
        }
    }
}

# Ejecutar función principal
Main
