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

dev: image up
