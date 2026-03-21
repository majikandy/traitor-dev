.PHONY: dev stop build logs shell migrate fresh

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
