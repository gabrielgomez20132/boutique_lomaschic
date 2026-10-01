#!/bin/bash
set -e

BASE_PATH="/var/www/proyectos_laravel/lomaschic"

echo "Configurando permisos Laravel..."
echo "Ruta base: ${BASE_PATH}"

# Crear directorios necesarios
mkdir -p ${BASE_PATH}/public/uploads
mkdir -p ${BASE_PATH}/storage/framework/sessions
mkdir -p ${BASE_PATH}/storage/framework/views
mkdir -p ${BASE_PATH}/storage/framework/cache
mkdir -p ${BASE_PATH}/storage/logs
mkdir -p ${BASE_PATH}/bootstrap/cache

# Permisos Laravel
chown -R www-data:www-data ${BASE_PATH}/storage
chown -R www-data:www-data ${BASE_PATH}/bootstrap/cache
chown -R www-data:www-data ${BASE_PATH}/public/uploads

chmod -R 775 ${BASE_PATH}/storage
chmod -R 775 ${BASE_PATH}/bootstrap/cache
chmod -R 775 ${BASE_PATH}/public/uploads

echo "Permisos configurados correctamente"

# Ejecutar comando principal
exec "$@"
