#!/bin/sh

set -e

envsubst < ./docker/app/conf.d/symfony.ini > "$PHP_INI_DIR/conf.d/symfony.ini"

if [ "${XDEBUG_ENABLED}" == "1" ]; then
    docker-php-ext-enable xdebug
fi

chown 1000:1000 /data

if [ "${APP_ENV}" == "prod" ]; then
    su app -c "bin/console cache:clear"
fi

su app -c 'bin/console app:setup'

exec "$@"
