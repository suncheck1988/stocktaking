init: init-ci
init-ci: docker-down-clear \
	api-clear \
	docker-pull docker-build docker-up \
	api-init
up: docker-up
down: docker-down
restart: down up
check: lint analyze validate-schema
lint: api-lint
analyze: api-analyze
validate-schema: api-validate-schema

update-deps: api-composer-update restart

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build --pull

api-clear:
	docker run --rm -v ${PWD}/:/app -w /app alpine sh -c 'rm -rf var/cache/* var/log/* var/test/* && mkdir -p var/log/nginx && touch var/log/nginx/access.log && touch var/log/nginx/error.log'

api-init: api-permissions api-composer-install api-wait-db api-migrations api-fixtures

api-permissions:
	docker run --rm -v ${PWD}/:/app -w /app alpine chmod 777 var/cache var/log var/test

api-composer-install:
	docker-compose run --rm php-cli composer install

api-composer-update:
	docker-compose run --rm php-cli composer update

api-wait-db:
	docker-compose run --rm php-cli wait-for-it postgres:5432 -t 30

api-migrations:
	docker-compose run --rm php-cli composer app migrations:migrate -- --no-interaction

api-fixtures:
	docker-compose run --rm php-cli composer app fixtures:load

api-check: api-validate-schema api-lint api-analyze

api-validate-schema:
	docker-compose run --rm php-cli composer app orm:validate-schema

api-lint:
	docker-compose run --rm php-cli composer lint
	docker-compose run --rm php-cli composer php-cs-fixer fix -- --dry-run --diff

api-cs-fix:
	docker-compose run --rm php-cli composer php-cs-fixer fix

api-analyze:
	docker-compose run --rm php-cli composer psalm -- --no-diff --no-cache

api-analyze-diff:
	docker-compose run --rm php-cli composer psalm

test: init-test-environment \
    test-unit \
	run-test-environment-migrations \
	test-functional \
	down-test-environment

init-test-environment:
	docker-compose -p waiter-test -f docker-compose.test.yml up --build -d

test-unit:
	docker-compose -p waiter-test -f docker-compose.test.yml run --rm php-cli_test vendor/bin/phpunit --testsuite=unit

run-test-environment-migrations:
	docker-compose -p waiter-test -f docker-compose.test.yml run --rm php-cli_test composer app migrations:migrate -n

test-functional:
	docker-compose -p waiter-test -f docker-compose.test.yml run --rm php-cli_test vendor/bin/phpunit --testsuite=functional

down-test-environment:
	docker-compose -p waiter-test -f docker-compose.test.yml down --remove-orphans