# Order Fulfillment Service (Laravel)

Backend service for handling order lifecycle, stock reservation, and fulfillment workflows.

The project models an internal e-commerce fulfillment system and demonstrates how to design backend logic for real business workflows.

The project demonstrates how to design and verify a backend that handles:

- create and manage orders  
- handle stock reservation to prevent overselling  
- process order lifecycle transitions  
- cancel orders with stock restoration  
- import external marketplace orders (mocked)  
- synchronize order statuses  
- ensure safe write operations using idempotency  

## Why this repository is worth reviewing

This is not a simple CRUD project. It is designed to demonstrate how a backend system can model real business workflows and remain maintainable.

Key focus areas:

- domain-oriented structure  
- clear separation of responsibilities  
- predictable and simple local setup  
- readable and structured codebase  

## Tech stack

- PHP 8.3+  
- Laravel 11  
- PostgreSQL  
- Redis  

## Core capabilities

- create and manage orders  
- handle stock reservation to prevent overselling  
- process order lifecycle transitions  
- cancel orders with stock restoration  
- import external marketplace orders (mocked)  
- synchronize order statuses  
- ensure safe write operations using idempotency  

## Architecture overview

The project is structured to keep business logic separate from framework concerns:

- `Application` — use-case orchestration  
- `Domain` — business rules and logic  
- `Infrastructure` — database, cache, integrations  
- `Http` — controllers and API layer  

This separation keeps the system easier to understand and extend.

## Key engineering decisions

- stock reservation is treated as a separate business concern  
- idempotency is used to prevent duplicate operations  
- Redis is used for coordination where needed  
- database remains the source of truth  
- external integrations are mocked  

## Order lifecycle

Orders move through defined states:

- created  
- paid  
- packed  
- shipped  
- delivered  
- cancelled  

## What I would discuss in an interview

- separation of domain logic from Laravel layer  
- stock reservation strategy  
- idempotency implementation  
- consistency and trade-offs  
- system structure and scalability considerations  

## Non-goals

This is a portfolio project, so some parts are intentionally simplified:

- no full authentication/authorization system  
- no real external integrations  
- no payment processing  
- no production deployment setup  

## Local setup

```bash
git clone https://github.com/PaulSavanna/laravel-order-fulfillment-service.git
cd laravel-order-fulfillment-service
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
