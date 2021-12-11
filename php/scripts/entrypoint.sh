#!/usr/bin/env bash

composer install
php bin/console doc:mig:mig --no-interaction
php bin/console doc:fix:load --no-interaction
php bin/console make:migration

exec "$@"