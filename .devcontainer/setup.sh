#!/usr/bin/env bash
# Prepares the project the first time a Codespace is created.
set -e

composer install --no-interaction --prefer-dist

if [ ! -f .env ]; then
    cp .env.example .env
    # Codespaces has no MySQL server, so use a simple SQLite file there
    sed -i 's/^DB_CONNECTION=mysql/DB_CONNECTION=sqlite/; s/^DB_DATABASE=.*/# DB_DATABASE=/' .env
    php artisan key:generate
fi

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    php artisan migrate --seed --force
fi

echo "✔ Ready — the website starts automatically on port 8000"
