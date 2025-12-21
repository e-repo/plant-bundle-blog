#!/bin/sh
export S3_ACCESS_KEY_ID=$(cat /run/secrets/s3_access_key)
export S3_SECRET_KEY=$(cat /run/secrets/s3_secret_key)

# Запустите основной процесс контейнера (например, php-fpm)
exec "$@"
