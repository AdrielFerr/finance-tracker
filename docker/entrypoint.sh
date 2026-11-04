#!/bin/sh
set -e

cd /var/www

echo "[entrypoint] Aguardando banco de dados responder..."
timeout=60
until php -r "new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
  timeout=$((timeout - 2))
  if [ "$timeout" -le 0 ]; then
    echo "[entrypoint] Timeout aguardando banco. Tentando migrar mesmo assim."
    break
  fi
  echo "[entrypoint] Banco ainda nao respondeu, tentando de novo em 2s..."
  sleep 2
done

echo "[entrypoint] Rodando migrations..."
php artisan migrate --force

echo "[entrypoint] Cacheando config, routes e views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[entrypoint] Iniciando supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
