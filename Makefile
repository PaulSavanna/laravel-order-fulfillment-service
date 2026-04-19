SHELL := /usr/bin/env bash
.DEFAULT_GOAL := help

.PHONY: help bootstrap qa test acceptance verify docker-up docker-bootstrap docker-verify docker-down

help:
	@printf '%s\n' \
	  'Available targets:' \
	  '  make bootstrap        Install dependencies and prepare a local SQLite verification environment' \
	  '  make qa               Run lint, contract, hygiene, and static smoke checks' \
	  '  make test             Run the PHPUnit/Laravel test suite' \
	  '  make acceptance       Run API smoke checks against a running app at $$APP_URL (default http://127.0.0.1:8000)' \
	  '  make verify           Run the full local verification flow (bootstrap + qa + tests + live API smoke)' \
	  '  make docker-up        Build and start the Docker stack' \
	  '  make docker-bootstrap Prepare the application inside Docker (composer, key, migrate, seed)' \
	  '  make docker-verify    Run the full Docker verification flow including live API smoke' \
	  '  make docker-down      Stop the Docker stack and remove volumes'

bootstrap:
	bash tools/bootstrap.sh --sqlite

qa:
	php tools/php-lint.php
	php tools/repo-hygiene.php
	php tools/validate-api-contracts.php
	php tools/static-smoke.php
	@if [ -x vendor/bin/pint ]; then php vendor/bin/pint --test; else echo 'vendor/bin/pint not available yet; run make bootstrap first'; fi

test:
	php artisan test

acceptance:
	bash tools/test-api.sh "$${APP_URL:-http://127.0.0.1:8000}"

verify:
	bash tools/verify.sh --sqlite


docker-up:
	docker compose up --build -d


docker-bootstrap:
	docker compose exec -T app bash ./tools/bootstrap.sh --docker --no-install


docker-verify:
	bash tools/docker-verify.sh


docker-down:
	docker compose down -v --remove-orphans
