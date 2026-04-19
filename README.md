# Laravel E-commerce Order Fulfillment Service

Demo portfolio project that models a **production-like internal e-commerce service** for order intake, stock reservation, shipment calculation, and marketplace synchronization. It uses only demo data, mock integrations, and safe example configuration so the repository can stay public.

The repository is designed to be **self-verifying**: either the checks pass, or the scripts and CI pipeline show exactly which step failed.

## Business context

The service represents the kind of backend used by an international e-commerce team that sells through direct and marketplace channels and operates around internal fulfillment workflows:

- direct order creation
- marketplace order import
- stock reservation with overselling protection
- shipment cost calculation
- controlled order status transitions
- cancellation with stock rollback
- asynchronous internal notifications
- outbound marketplace status synchronization
- retry-safe idempotent order creation

## Stack

- PHP 8.3+
- Laravel 11
- PostgreSQL
- Redis
- Docker / Docker Compose
- PHPUnit
- Laravel Pint
- OpenAPI + Postman collection

## Main capabilities

- Create orders with multiple items
- Reserve stock atomically with lock protection
- Prevent overselling
- Calculate shipment cost through a mock shipping provider
- Enforce valid order status transitions
- Release reserved stock on cancellation
- Consume reserved stock on shipment
- Import marketplace orders through a mock adapter
- Sync marketplace order status through queued jobs
- Use idempotency keys and payload fingerprints to make write operations retry-safe
- Cache product lookups
- Rate-limit write-heavy endpoints

## Architecture overview

```text
app/
├── Domain/            # domain contracts, enums, exceptions, events
├── Application/       # use cases, handlers, DTOs, orchestration services
├── Infrastructure/    # Eloquent repositories, cache/idempotency, mock integrations
└── Http/              # controllers, requests, middleware, resources
```

### Core domains

- **Product** — sellable catalog item
- **Stock** — `available_quantity` + `reserved_quantity`
- **Order** — main aggregate, totals, lifecycle status, idempotency metadata
- **OrderItem** — purchased item snapshot
- **Shipment** — delivery state for the order
- **Customer** — buyer identity and shipping address
- **SalesChannel** — `direct`, `amazon`, `ebay`

## API summary

### Orders

- `GET /api/orders`
- `POST /api/orders`
- `GET /api/orders/{id}`
- `POST /api/orders/{id}/pay`
- `POST /api/orders/{id}/pack`
- `POST /api/orders/{id}/ship`
- `POST /api/orders/{id}/deliver`
- `POST /api/orders/{id}/cancel`

### Products

- `GET /api/products`
- `GET /api/products/{id}`

### Shipments

- `GET /api/shipments/{id}`

### Marketplace

- `POST /api/marketplace/orders/import`

## Repository self-verification model

This repository ships with executable verification layers:

- **bootstrap** — dependency install + environment preparation + migrations + seeders
- **qa** — PHP lint, repo hygiene, API contract consistency, static business smoke checks, Pint
- **test** — full Laravel / PHPUnit test suite
- **acceptance** — live HTTP smoke checks against a running application
- **verify** — one command that runs the full local verification path
- **docker-verify** — one command that validates the Docker path end-to-end
- **GitHub Actions CI** — automatic checks on push / pull request

## Quick start

### Option A — local verification with PHP + Composer

This path uses a local SQLite file and sync/file drivers so you can verify the repository without setting up PostgreSQL and Redis manually.

```bash
cp .env.example .env
bash tools/verify.sh --sqlite
```

Equivalent Make target:

```bash
make verify
```

### Option B — Docker verification with PostgreSQL + Redis

This path validates the intended runtime stack.

```bash
bash tools/docker-verify.sh
```

Equivalent Make target:

```bash
make docker-verify
```

## Local verification

### Minimal prerequisites

- PHP 8.3+
- Composer
- curl

### One-command local verification

```bash
bash tools/verify.sh --sqlite
```

What this does:

1. validates and installs Composer dependencies
2. prepares `.env`
3. configures a local SQLite verification database
4. generates `APP_KEY`
5. runs `migrate:fresh --seed`
6. checks `route:list`
7. runs lint + hygiene + contract checks
8. runs the Laravel test suite
9. starts `php artisan serve`
10. runs live API smoke checks through HTTP

## Docker verification

### Prerequisites

- Docker with `docker compose`
- curl

### One-command Docker verification

```bash
bash tools/docker-verify.sh
```

What this does:

1. validates `docker compose config`
2. builds and starts the stack
3. bootstraps the app container
4. runs QA checks inside Docker
5. runs the Laravel test suite inside Docker
6. waits for the HTTP endpoint
7. runs live API smoke checks against `http://127.0.0.1:8080`

### Manual Docker path

```bash
docker compose up --build -d
docker compose exec -T app bash ./tools/bootstrap.sh --docker --no-install
docker compose exec -T app php artisan test
bash tools/test-api.sh http://127.0.0.1:8080
```

## CI verification

GitHub Actions workflow: `.github/workflows/ci.yml`

It performs two jobs:

### 1. `laravel-ci`

- `composer validate --no-check-publish`
- `composer install`
- environment preparation
- `php artisan key:generate`
- `php artisan optimize:clear`
- `php tools/php-lint.php`
- `php tools/repo-hygiene.php`
- `php tools/validate-api-contracts.php`
- `php tools/static-smoke.php`
- `php vendor/bin/pint --test`
- `php artisan migrate:fresh --seed`
- `php artisan route:list`
- `php artisan test`
- starts `php artisan serve`
- runs `bash tools/test-api.sh http://127.0.0.1:8000`

### 2. `docker-smoke`

- `docker compose config`
- `bash tools/docker-verify.sh`

## Acceptance checklist

Before treating the repository as publish-ready, run at least one of these complete flows after the latest change:

```bash
bash tools/verify.sh --sqlite
```

or

```bash
bash tools/docker-verify.sh
```

The stricter checklist is also documented in `docs/publish-readiness-checklist.md`.

## Available commands

### Make targets

```bash
make bootstrap
make qa
make test
make acceptance
make verify
make docker-up
make docker-bootstrap
make docker-verify
make docker-down
```

### Useful scripts

```bash
bash tools/bootstrap.sh --sqlite
bash tools/verify.sh --sqlite
bash tools/docker-verify.sh
bash tools/test-api.sh http://127.0.0.1:8000
bash tools/next-run-checks.sh
```

## Key scenarios covered by tests and smoke checks

- order creation with multiple items
- stock reservation
- overselling rejection
- valid order lifecycle transitions
- invalid transition rejection
- cancellation with stock release
- shipment state updates during fulfillment
- idempotent order creation
- idempotency conflict on changed payload
- marketplace import deduplication
- marketplace cross-channel import separation
- API contract consistency across routes / README / OpenAPI / Postman

## Example API usage

### Create order

```http
POST /api/orders
Idempotency-Key: order-123
Content-Type: application/json
```

```json
{
  "customer": {
    "email": "buyer@example.com",
    "first_name": "Eva",
    "last_name": "Stone",
    "country_code": "DE",
    "city": "Berlin",
    "address_line_1": "Street 1",
    "postal_code": "10117"
  },
  "sales_channel_code": "direct",
  "currency": "EUR",
  "items": [
    {"product_id": 1, "quantity": 2},
    {"product_id": 2, "quantity": 1}
  ]
}
```

### List products

```http
GET /api/products?search=hoodie&sort_by=price_amount&direction=desc&per_page=10
```

### List orders

```http
GET /api/orders?status=paid&customer_email=buyer@example.com&sort_by=created_at&direction=desc&per_page=20
```

## Data and integrations

This repository is safe for a public portfolio:

- seeders create demo catalog and sales-channel data
- factories produce demo-only records
- marketplace and shipping integrations are mock implementations
- `.env.example` contains only example values
- no real secrets or external credentials are required

## Common failure points

### `composer install` fails

Check:

- PHP version is 8.3+
- required extensions are installed (`pdo_sqlite`, `pdo_pgsql`, `intl`, `bcmath`)
- Composer is available in `PATH`

### `php artisan migrate:fresh --seed` fails locally

Most often caused by:

- missing SQLite extension for the local verification path
- stale `.env` values pointing to an unavailable DB

Fast fix:

```bash
rm -f .env
bash tools/bootstrap.sh --sqlite
```

### Docker starts but the app is not reachable

Check:

- `docker compose ps`
- `docker compose logs app`
- `docker compose logs nginx`
- `docker compose logs postgres`
- `docker compose logs redis`

Then rerun:

```bash
bash tools/docker-verify.sh
```

### API smoke checks fail

Run the steps manually:

```bash
php artisan route:list
php artisan test
bash tools/test-api.sh http://127.0.0.1:8000
```

## What passing validation means

If **`bash tools/verify.sh --sqlite`** passes, then the repository has demonstrated:

- dependencies install successfully
- local Laravel bootstrap works
- migrations and seeders work from a clean database
- routes resolve
- static / hygiene / contract checks pass
- automated tests pass
- the app can serve live HTTP requests
- critical API flows behave as expected in a reproducible local environment

If **`bash tools/docker-verify.sh`** passes, then the Docker runtime path has additionally demonstrated:

- `docker compose config` is valid
- the app, PostgreSQL, Redis, and nginx start coherently
- container bootstrapping works
- live API checks pass on the Docker stack

## Documentation and artifacts

- OpenAPI: `docs/openapi.yaml`
- Postman collection: `docs/postman/OrderFulfillmentService.postman_collection.json`
- Publish checklist: `docs/publish-readiness-checklist.md`
- Validation report for this generated archive: `docs/final-validation-report.md`
