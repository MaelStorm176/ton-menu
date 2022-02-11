#!/usr/bin/env bash

composer install
php bin/console doctrine:migrations:diff
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load -n
exec "$@"