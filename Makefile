.PHONY: dev stop build

dev:
	docker compose up --build

stop:
	docker compose down

build:
	docker compose build --no-cache
