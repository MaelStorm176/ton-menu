#!/usr/bin/env bash

composer install
bin/console doc:mig:mig --no-interaction
bin/console doc:fix:load --no-interaction

exec "$@"