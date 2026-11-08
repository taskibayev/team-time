#!/bin/sh
set -eu
until php bin/console doctrine:query:sql 'SELECT 1' >/dev/null 2>&1; do sleep 1; done
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:seed --no-interaction
exec "$@"
