image:
	@echo "==> Building docker image"
	@docker-compose build

SKIP_TESTS ?= 0

.PHONY: env
env:
	@if [ ! -f .env ]; then \
		echo "==> Creating .env from .env.example"; \
		cp .env.example .env; \
	else \
		echo "==> .env already exists"; \
	fi

down:
	@echo "==> Stopping containers"
	docker-compose down

down-v:
	@echo "==> Stopping containers and removing volumes"
	docker-compose down -v

migrate:
	@echo "==> Running migrations"
	docker-compose exec -ti simple-payment-api php bin/hyperf.php migrate

rollback:
	@echo "==> Rolling back last migration batch"
	docker-compose exec -ti simple-payment-api php bin/hyperf.php migrate:rollback

fresh:
	@echo "==> Rebuilding database schema (fresh)"
	docker-compose exec -ti simple-payment-api php bin/hyperf.php migrate:fresh

tests:
	@if [ "$(SKIP_TESTS)" = "1" ]; then \
		echo "Skipping tests (SKIP_TESTS=1)"; \
	else \
		echo "==> Running full test suite"; \
		docker-compose exec -ti simple-payment-api composer test; \
	fi

test-filter:
	@if [ -z "$(filter)" ]; then \
		echo "Missing filter. Usage: make test-filter filter=Pattern"; \
		exit 1; \
	fi
	@if [ "$(SKIP_TESTS)" = "1" ]; then \
		echo "Skipping filtered tests (SKIP_TESTS=1)"; \
	else \
		echo "==> Running filtered tests with filter=$(filter)"; \
		docker-compose exec -ti simple-payment-api composer test -- --filter $(filter); \
	fi

coverage:
	@echo "==> Generating coverage report"
	docker-compose exec -ti simple-payment-api composer test -- --coverage-html /opt/www/runtime/coverage
	open ./runtime/coverage/index.html

fix:
	@echo "==> Running code style fixer"
	docker-compose exec -ti simple-payment-api composer cs-fix

analyse:
	@echo "==> Running static analysis"
	docker-compose exec -ti simple-payment-api composer analyse

sleep:
	@echo "==> Sleeping for 10 seconds"
	@sleep 10

install:
	@echo "==> Installing dependencies"
	docker-compose exec -ti simple-payment-api composer install

start-stack:
	@echo "==> Starting all containers"
	docker-compose up -d

logs:
	@echo "==> Tailing all container logs"
	docker-compose logs -f

integration-logs:
	@echo "==> Tailing integration logs"
	tail -f ./runtime/logs/integration.log

up: env image start-stack sleep migrate
	@echo "==> Stack ready"
