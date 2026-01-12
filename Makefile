image:
	@echo "Building docker image..."
	@docker-compose build

SKIP_TESTS ?= 0

up:
	@echo "Uping all containers..."
	docker-compose up -d

down:
	@echo "Downing all containers..."
	docker-compose down

down-v:
	@echo "Downing all containers and volumes..."
	docker-compose down -v

migrate:
	@echo "Migrating database..."
	docker-compose exec -ti simple-payment-api php bin/hyperf.php migrate

rollback:
	@echo "Migrating database..."
	docker-compose exec -ti simple-payment-api php bin/hyperf.php migrate:rollback

fresh:
	@echo "Migrating database..."
	docker-compose exec -ti simple-payment-api php bin/hyperf.php migrate:fresh

tests:
	@if [ "$(SKIP_TESTS)" = "1" ]; then \
		echo "Skipping tests (SKIP_TESTS=1)"; \
	else \
		echo "Running tests"; \
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
		echo "Running filtered tests with filter=$(filter)"; \
		docker-compose exec -ti simple-payment-api composer test -- --filter $(filter); \
	fi

sleep:
	@sleep 5

dev: image up sleep migrate
