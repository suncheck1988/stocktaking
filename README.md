# Stocktaking

### Настройки
Скопировать `.env.dist` в `.env`

### Сборка проекта в первый раз (происходит полный сброс БД)
`make init`

### Повторная сборка контейнеров
`docker-compose down && docker-compose up --build -d`

### Запуск проверок (linter, cs-fixer, psalm)
`make api-check`

### Запуск тестов
`make test`

### Swagger доступен по адресу
`/doc`

### Команда для генерации новой миграции
`docker-compose run --rm php-cli composer app migrations:diff`

### Команда для применения миграций
`docker-compose run --rm php-cli composer app migrations:migrate`

### Команда для применения одной конкретной миграции
`docker-compose run --rm php-cli php bin/console.php migrations:execute --up 'App\Data\Migration\Version20230514140813'`

### Команда для отката одной конкретной миграции
`docker-compose run --rm php-cli php bin/console.php migrations:execute --down 'App\Data\Migration\Version20230514140813'`

### Запуск консольных команд:
`docker-compose run --rm php-cli php bin/console.php COMMAND_NAME`