.PHONY: start stop marmiton purge install marmiton-check no_targets__ list

## Variables d'environnement
EXEC_PHP 		= php
SYMFONY 		= symfony
SYMFONY_CONSOLE = $(SYMFONY) console
DOCKER 			= docker
COMPOSER 		= composer
NPM 			= npm


## Listes les commandes de make
no_targets__:
list:
	sh -c "$(MAKE) -p no_targets__ | awk -F':' '/^[a-zA-Z0-9][^\$$#\/\\t=]*:([^=]|$$)/ {split(\$$1,A,/ /);for(i in A)print A[i]}' | grep -v '__\$$' | sort"

## Démarre les containers
start:
	@$(DOCKER) compose --env-file=".env.local" up -d

down:
	@$(DOCKER) compose --env-file=".env.local" down -v

## Arrete les containers
stop:
	@$(DOCKER) compose --env-file=".env.local" stop

## Execute le script marmiton
marmiton:
	@$(NPM) run marmiton
	@$(SYMFONY_CONSOLE) doctrine:fixtures:load -n

## Nettoye le cache
cache-clear:
	@$(SYMFONY_CONSOLE) cache:clear

## Purge les logs
purge:
	rm -rf var/cache/*
	rm -rf var/log/*

## Install les dépendances composer et npm
install:
	@$(COMPOSER) install --no-progress --prefer-dist --optimize-autoloader
	@$(NPM) install

run:
	@$(SYMFONY) server:start -d
	@$(NPM) run build
	mysqld --console

## Build la DB en fonction des migrations et execute le script marmiton
marmiton-check:
	@$(SYMFONY_CONSOLE) doctrine:cache:clear-metadata
	@$(SYMFONY_CONSOLE) doctrine:database:create --if-not-exists
	@$(SYMFONY_CONSOLE) doctrine:schema:drop --force
	@$(SYMFONY_CONSOLE) doctrine:schema:create
	@$(SYMFONY_CONSOLE) doctrine:schema:validate
	make marmiton
