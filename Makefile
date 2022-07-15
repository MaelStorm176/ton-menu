.PHONY: start stop marmiton purge install marmiton-check no_targets__ list

## Variables d'environnement
EXEC_PHP 		= php
SYMFONY 		= symfony
SYMFONY_CONSOLE = $(SYMFONY) console
DOCKER 			= docker
DOCKER_COMPOSE 	= $(DOCKER) compose --env-file=".env.local"
DOCKER_RUN 		= $(DOCKER_COMPOSE) run --rm
COMPOSER 		= composer
NPM 			= npm


## Listes les commandes de make
no_targets__:
list:
	sh -c "$(MAKE) -p no_targets__ | awk -F':' '/^[a-zA-Z0-9][^\$$#\/\\t=]*:([^=]|$$)/ {split(\$$1,A,/ /);for(i in A)print A[i]}' | grep -v '__\$$' | sort"

## Build les images
build:
	@$(DOCKER_COMPOSE) build

## Démarre les containers
start:
	@$(DOCKER_COMPOSE) up -d

down:
	@$(DOCKER_COMPOSE) down --remove-orphans

## Arrete les containers
stop:
	@$(DOCKER_COMPOSE) stop

## Nettoye le cache
cache-clear:
	$(DOCKER_RUN) php $(SYMFONY_CONSOLE) cache:clear

## Purge les logs
purge:
	rm -rf var/cache/*
	rm -rf var/log/*

## Install les dépendances de l'application (composer)
composer-install:
	$(DOCKER_RUN) composer $(COMPOSER) install --prefer-dist --no-interaction --no-progress --no-suggest --optimize-autoloader

## Install une dépendance choisie (composer)
composer-require:
	$(DOCKER_RUN) composer $(COMPOSER) require

## Install les dépendances de l'application (npm)
npm-install:
	$(DOCKER_RUN) encore $(NPM) install

## Webpack (npm) Encore
npm-watch:
	$(DOCKER_RUN) encore $(NPM) run watch
npm-dev:
	$(DOCKER_RUN) encore $(NPM) run dev
npm-build:
	$(DOCKER_RUN) encore $(NPM) run build

## Install les dépendances composer + npm
install:
	make composer-install
	make npm-install

## Construit toute la base de données
build-db:
	@$(DOCKER_RUN) php $(SYMFONY_CONSOLE) doctrine:cache:clear-metadata
	@$(DOCKER_RUN) php $(SYMFONY_CONSOLE) doctrine:database:create --if-not-exists
	@$(DOCKER_RUN) php $(SYMFONY_CONSOLE) doctrine:schema:drop --force
	@$(DOCKER_RUN) php $(SYMFONY_CONSOLE) doctrine:schema:create
	@$(DOCKER_RUN) php $(SYMFONY_CONSOLE) doctrine:schema:validate

migration:
	@$(DOCKER_RUN) php $(SYMFONY_CONSOLE) make:migration --no-interaction

migrate:
	@$(DOCKER_RUN) php $(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction

## Créer une nouvelle entité
new-entity:
	@$(DOCKER_RUN) php $(SYMFONY_CONSOLE) make:entity

## Build la DB en fonction des migrations et execute le script marmiton
marmiton-check:
	make build-db
	make marmiton

## Execute le script marmiton
marmiton:
	@$(DOCKER_RUN) encore $(NPM) run marmiton
	@$(DOCKER_RUN) php $(SYMFONY_CONSOLE) doctrine:fixtures:load -n

