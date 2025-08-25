#!/bin/bash
set -euo pipefail

echo "=== Iniciando configuración de la aplicación ==="

# --- Config ---
APP_DIR="/var/www"
APP_USER="${APP_USER:-www-data}"   # sobrescribe con env si quieres otro usuario (p.ej. nginx)
STORAGE_DIR="$APP_DIR/storage"
BOOTSTRAP_CACHE_DIR="$APP_DIR/bootstrap/cache"

# Función para esperar a que un servicio esté disponible
wait_for_service() {
  local host=$1
  local port=$2
  local service_name=$3

  echo "Esperando a que $service_name esté disponible en $host:$port..."
  for i in {1..30}; do
    if nc -z "$host" "$port"; then
      echo "$service_name está disponible!"
      return 0
    fi
    echo "Intento $i/30: $service_name no está disponible aún..."
    sleep 2
  done
  echo "Error: $service_name no está disponible después de 60 segundos"
  exit 1
}

# --- Esperar a dependencias ---
wait_for_service "db" "5432" "PostgreSQL"

# --- Asegurar estructura y permisos de Laravel ---
echo "=== Preparando storage/ y bootstrap/cache ==="
mkdir -p \
  "$STORAGE_DIR/framework/cache" \
  "$STORAGE_DIR/framework/sessions" \
  "$STORAGE_DIR/framework/testing" \
  "$STORAGE_DIR/framework/views" \
  "$STORAGE_DIR/logs" \
  "$BOOTSTRAP_CACHE_DIR"

# Archivo de log
touch "$STORAGE_DIR/logs/laravel.log"

# Dueños & permisos (775 directorios, 664 archivos)
chown -R "$APP_USER:$APP_USER" "$STORAGE_DIR" "$BOOTSTRAP_CACHE_DIR"
find "$STORAGE_DIR" -type d -exec chmod 775 {} \;
find "$STORAGE_DIR" -type f -exec chmod 664 {} \;
chmod 775 "$BOOTSTRAP_CACHE_DIR"

# --- Limpiar compilados previos ---
echo "=== Limpiando caches previas ==="
php artisan optimize:clear || true
rm -rf "$STORAGE_DIR/framework/views/"* || true

# --- Migraciones & seeders ---
echo "=== Ejecutando migraciones ==="
php artisan migrate --force
echo "✅ Migraciones completadas"

echo "=== Ejecutando seeders ==="
php artisan db:seed --force
echo "✅ Seeders completados"


# --- Cachear config/rutas/vistas ---
echo "=== Construyendo caches de aplicación ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎉 Inicialización completada exitosamente"
