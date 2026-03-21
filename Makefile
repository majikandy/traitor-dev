.PHONY: dev stop build artisan

dev:
	docker compose up --build

stop:
	docker compose down

build:
	docker compose build --no-cache

artisan:
	docker compose exec app php artisan $(cmd)
