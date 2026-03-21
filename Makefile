.PHONY: dev stop build create publish rollback sites status

dev:
	docker compose up --build

stop:
	docker compose down

build:
	docker compose build --no-cache

create:
	docker compose exec app php artisan site:create $(domain) --template=$(or $(template),business)

publish:
	docker compose exec app php artisan site:publish $(domain)

rollback:
	docker compose exec app php artisan site:rollback $(domain)

sites:
	docker compose exec app php artisan site:list

status:
	docker compose exec app php artisan site:status $(domain)
