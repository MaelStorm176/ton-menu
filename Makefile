.PHONY: start stop

start:
	docker compose --env-file=".env.local" up -d

stop:
	docker compose down

marmiton:
	npm run marmiton
	symfony console doctrine:fixtures:load -n