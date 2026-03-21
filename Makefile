.PHONY: help dev stop build logs shell migrate fresh

.DEFAULT_GOAL := help

# Show available commands
help:
	@echo "Usage: make <target>"
	@echo ""
	@echo "Targets:"
	@echo "  dev      Start everything (foreground with logs)"
	@echo "  up       Start in background"
	@echo "  stop     Stop all containers"
	@echo "  build    Rebuild containers (no cache)"
	@echo "  logs     Tail container logs"
	@echo "  shell    Drop into the PHP container"
	@echo "  migrate  Run migrations"
	@echo "  fresh    Nuclear reset — wipe volumes and rebuild"

# Start everything (foreground so you see logs)
dev:
	docker compose up --build

# Start in background
up:
	docker compose up -d --build

stop:
	docker compose down

build:
	docker compose build --no-cache

logs:
	docker compose logs -f

# Drop into the PHP container
shell:
	docker compose exec app bash

# Run migrations manually
migrate:
	docker compose exec app php artisan migrate --force

# Nuclear reset — wipe volumes and rebuild
fresh:
	docker compose down -v
	docker compose up --build
