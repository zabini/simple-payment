image:
	@echo "Building docker image..."
	@docker-compose build

up:
	@echo "Uping all containers..."
	docker-compose up -d

down:
	@echo "Downing all containers..."
	docker-compose down

down-v:
	@echo "Downing all containers and volumes..."
	docker-compose down -v

tests:
	@if [ -z "$(filter)" ]; then \
		echo "Missing filter. Usage: make test-filter filter=Pattern"; \
		exit 1; \
	fi
	@echo "Running filtered tests with filter=$(filter)"
	@docker-compose exec -ti simple-payment-api composer test -- --filter $(filter)

dev: image up
