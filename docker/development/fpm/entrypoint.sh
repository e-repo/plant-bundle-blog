#!/bin/sh
set -e

# Читаем секреты из файлов
if [ -f /run/secrets/s3_access_key ]; then
    export S3_ACCESS_KEY_ID=$(cat /run/secrets/s3_access_key)
fi

if [ -f /run/secrets/s3_secret_key ]; then
    export S3_SECRET_KEY=$(cat /run/secrets/s3_secret_key)
fi

# Запускаем основной процесс
exec "$@"
