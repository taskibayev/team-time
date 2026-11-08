#!/bin/sh
set -eu

database_attempt=1
database_max_attempts=30

until php bin/console doctrine:query:sql 'SELECT 1' >/dev/null 2>&1; do
    if [ "$database_attempt" -ge "$database_max_attempts" ]; then
        echo "Database did not become ready after $database_max_attempts attempts." >&2
        php bin/console doctrine:query:sql 'SELECT 1'
        exit 1
    fi

    echo "Waiting for database ($database_attempt/$database_max_attempts)..."
    database_attempt=$((database_attempt + 1))
    sleep 1
done

php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:seed --no-interaction
exec "$@"
