#!/bin/bash
set -euo pipefail

echo "=== Iniciando configuración de la aplicación ==="

# --- Config ---
APP_DIR="/var/www"
APP_USER="${APP_USER:-www-data}"     # p.ej. www-data, nginx
APP_GROUP="${APP_GROUP:-}"           # si no viene, se detecta
STORAGE_DIR="$APP_DIR/storage"
BOOTSTRAP_CACHE_DIR="$APP_DIR/bootstrap/cache"
VENDOR_DIR="$APP_DIR/vendor"

# Resolver grupo si no se pasó por env
if [ -z "$APP_GROUP" ]; then
  if id "$APP_USER" &>/dev/null; then
    APP_GROUP="$(id -gn "$APP_USER")"
  else
    APP_GROUP="$APP_USER"
  fi
fi

# Detectar entorno para decidir caches
APP_ENV_VALUE="${APP_ENV:-local}"

# --- Fix de permisos del volumen vendor ---
echo "=== Verificando permisos del volumen vendor ==="
if [ -d "$VENDOR_DIR" ]; then
  echo "Ajustando permisos de $VENDOR_DIR"
  chown -R "$APP_USER:$APP_GROUP" "$VENDOR_DIR" || true
  chmod -R 775 "$VENDOR_DIR" || true
fi

# -----------------------------
# DEPENDENCIAS
# -----------------------------
if [ ! -f "vendor/autoload.php" ]; then
  echo "=== 📦 Instalando dependencias de Composer ==="

  # Ejecutar composer como www-data
  if [ "$APP_ENV_VALUE" = "production" ]; then
    su -s /bin/bash "$APP_USER" -c "composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-ansi"
  else
    su -s /bin/bash "$APP_USER" -c "composer install --optimize-autoloader --no-interaction --prefer-dist --no-ansi"
  fi
  
  # Asegurar permisos correctos después de la instalación
  chown -R "$APP_USER:$APP_GROUP" "$VENDOR_DIR" || true
  chmod -R 775 "$VENDOR_DIR" || true
else
  echo "ℹ️  Dependencias ya instaladas, se omite Composer install."
fi
# --- Espera de dependencias ---
wait_for_service() {
  local host=$1 port=$2 service_name=$3
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

wait_for_service "db" "5432" "PostgreSQL"

# --- Estructura y permisos ---
echo "=== Preparando storage/ y bootstrap/cache ==="
mkdir -p \
  "$STORAGE_DIR/framework/cache" \
  "$STORAGE_DIR/framework/sessions" \
  "$STORAGE_DIR/framework/testing" \
  "$STORAGE_DIR/framework/views" \
  "$STORAGE_DIR/logs" \
  "$BOOTSTRAP_CACHE_DIR"

touch "$STORAGE_DIR/logs/laravel.log" || true

# Fix de permisos: dueños y modos (775 dirs, 664 files)
echo "=== Ajustando dueños y permisos (${APP_USER}:${APP_GROUP}) ==="
chown -R "$APP_USER:$APP_GROUP" "$STORAGE_DIR" "$BOOTSTRAP_CACHE_DIR" "$VENDOR_DIR" || true
find "$STORAGE_DIR" -type d -exec chmod 775 {} \; || true
find "$STORAGE_DIR" -type f -exec chmod 664 {} \; || true
chmod 775 "$BOOTSTRAP_CACHE_DIR" || true
chmod -R 775 "$VENDOR_DIR" || true

# --- Limpiar compilados previos ---
echo "=== Limpiando caches previas ==="
su -s /bin/bash "$APP_USER" -c "php artisan optimize:clear" || true
rm -rf "$STORAGE_DIR/framework/views/"* || true

# --- Migraciones & seeders ---
echo "=== Ejecutando migraciones ==="
su -s /bin/bash "$APP_USER" -c "php artisan migrate --force --no-interaction"
echo "✅ Migraciones completadas"

echo "=== Ejecutando seeders ==="
su -s /bin/bash "$APP_USER" -c "php artisan db:seed --force --no-interaction"
echo "✅ Seeders completados"

# --- Cachear SOLO en producción ---
if [ "$APP_ENV_VALUE" = "production" ]; then
  echo "=== Construyendo caches de aplicación (APP_ENV=production) ==="
  su -s /bin/bash "$APP_USER" -c "php artisan config:cache"
  su -s /bin/bash "$APP_USER" -c "php artisan route:cache"
  su -s /bin/bash "$APP_USER" -c "php artisan view:cache"
else
  echo "=== Omitiendo caches pesadas (APP_ENV=$APP_ENV_VALUE) ==="
fi

echo "🎉 Inicialización completada exitosamente"
