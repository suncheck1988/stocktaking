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

build:
	docker --log-level=debug build --pull --file=docker/production/nginx/Dockerfile --tag=${REGISTRY}/stocktaking-api-nginx:${IMAGE_TAG} .
	docker --log-level=debug build --pull --file=docker/production/php-fpm/Dockerfile --tag=${REGISTRY}/stocktaking-api-php-fpm:${IMAGE_TAG} .
	docker --log-level=debug build --pull --file=docker/production/php-cli/Dockerfile --tag=${REGISTRY}/stocktaking-api-php-cli:${IMAGE_TAG} .

push:
	docker push ${REGISTRY}/stocktaking-api-nginx:${IMAGE_TAG}
	docker push ${REGISTRY}/stocktaking-api-php-fpm:${IMAGE_TAG}
	docker push ${REGISTRY}/stocktaking-api-php-cli:${IMAGE_TAG}

validate-jenkins:
	curl --user ${USER} -X POST -F "jenkinsfile=<Jenkinsfile" ${HOST}/pipeline-model-converter/validate

deploy:
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'rm -rf site_${BUILD_NUMBER} && mkdir site_${BUILD_NUMBER}'

	envsubst < docker-compose-production.yml > docker-compose-production-env.yml
	scp -o StrictHostKeyChecking=no -P ${PORT} docker-compose-production-env.yml deploy@${HOST}:site_${BUILD_NUMBER}/docker-compose.yml
	rm -f docker-compose-production-env.yml

	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && echo "COMPOSE_PROJECT_NAME=stocktaking-api" >> .env'
	ssh -o StrictHostKeyChecking=no -P ${PORT} 'cd site_${BUILD_NUMBER} && echo "POSTGRES_PASSWORD=${POSTGRES_PASSWORD}" >> .env'
	ssh -o StrictHostKeyChecking=no -P ${PORT} 'cd site_${BUILD_NUMBER} && echo "MAILER_HOST=${MAILER_HOST}" >> .env'
	ssh -o StrictHostKeyChecking=no -P ${PORT} 'cd site_${BUILD_NUMBER} && echo "MAILER_PORT=${MAILER_PORT}" >> .env'
	ssh -o StrictHostKeyChecking=no -P ${PORT} 'cd site_${BUILD_NUMBER} && echo "MAILER_USERNAME=${MAILER_USERNAME}" >> .env'
	ssh -o StrictHostKeyChecking=no -P ${PORT} 'cd site_${BUILD_NUMBER} && echo "MAILER_PASSWORD=${MAILER_PASSWORD}" >> .env'
	ssh -o StrictHostKeyChecking=no -P ${PORT} 'cd site_${BUILD_NUMBER} && echo "MAILER_FROM_EMAIL=${MAILER_FROM_EMAIL}" >> .env'

	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker compose up --build --remove-orphans -d'
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'rm -f site'
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'ln -sr site_${BUILD_NUMBER} site'

deploy-clean:
	rm -f docker-compose-production-env.yml

rollback:
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker stack deploy --compose-file docker-compose.yml stocktaking --with-registry-auth --prune'