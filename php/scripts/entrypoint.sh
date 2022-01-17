#!/usr/bin/env bash

composer install
php bin/console doctrine:migrations:diff
php bin/console doctrine:schema:update --force
exec "$@"