# Vanilla Cloud sample API

## Setup container
```bash
docker-compose build
docker-compose up
```

## Setup database
```bash
docker-compose exec php bin/console doctrine:database:create --if-not-exists
docker-compose exec php bin/console doctrine:migrations:migrate -n
docker-compose exec php bin/console hautelook:fixtures:load --no-bundles -n
```

## API token setup and usage
1. Decide on a token value to use
2. Create an MD5 hash of that value
3. Set the hashed value as `API_TOKEN` in `.env.local` and `.env.test.local`
4. Use the original token value as the value of the `X-AUTH-TOKEN` header value when making API requests

## Usage
List available endpoints
```bash
php ./bin/console debug:router
```

# Testing

### Setup
```bash
php ./bin/console doctrine:database:create --if-not-exists --env=test
php ./bin/console doctrine:migrations:migrate -n --env=test

SYMFONY_PHPUNIT_VERSION=8
export SYMFONY_PHPUNIT_VERSION
php ./bin/phpunit
```

### Running tests
```bash
php ./bin/phpunit
```
or with php-xdebug installed locally
```bash
php ./bin/phpunit --coverage-html coverage --coverage-text
```
