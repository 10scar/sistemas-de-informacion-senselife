.PHONY: up down test logs

up:
	docker compose up --build -d

down:
	docker compose down

test:
	docker compose run --rm -e INTERNAL_TOKEN=test-token -e MONGODB_DATABASE=senselife_data_test api pytest -q

logs:
	docker compose logs -f api
