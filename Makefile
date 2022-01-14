.PHONY: start stop marmiton purge install marmiton-check no_targets__ list

## Variables d'environnement
EXEC_PHP 		= php
SYMFONY 		= symfony
SYMFONY_CONSOLE = $(SYMFONY) console
DOCKER 			= docker
COMPOSER 		= composer
NPM 			= npm

start:
	@$(DOCKER) compose --env-file=".env.local" up -d

stop:
	@$(DOCKER) compose down

marmiton:
	@$(NPM) run marmiton
	@$(SYMFONY_CONSOLE) doctrine:fixtures:load -n

purge:
	rm -rf var/cache/*
	rm -rf var/log/*

install:
	@$(COMPOSER) install --no-progress --prefer-dist --optimize-autoloader
	@$(NPM) install

marmiton-check: ## Build the DB, control the schema validity, load fixtures and check the migration status
	@$(SYMFONY_CONSOLE) doctrine:cache:clear-metadata
	@$(SYMFONY_CONSOLE) doctrine:database:create --if-not-exists
	@$(SYMFONY_CONSOLE) doctrine:schema:drop --force
	@$(SYMFONY_CONSOLE) doctrine:schema:create
	@$(SYMFONY_CONSOLE) doctrine:schema:validate
	make marmiton

no_targets__:
list:
	sh -c "$(MAKE) -p no_targets__ | awk -F':' '/^[a-zA-Z0-9][^\$$#\/\\t=]*:([^=]|$$)/ {split(\$$1,A,/ /);for(i in A)print A[i]}' | grep -v '__\$$' | sort"
