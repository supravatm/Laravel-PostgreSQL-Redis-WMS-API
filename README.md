# Warehouse Management System (WMS)

A production-minded backend system for managing warehouses, storage locations, inventory, stock movements, and warehouse operations.

## Overview

This project implements a small Warehouse Management System focused on inventory accuracy, transactional stock operations, concurrency handling, REST APIs, Redis usage, testing, and clean backend architecture.

## Features

* User authentication and authorization
* Admin and warehouse operator roles
* Product management
* Warehouse management
* Storage location management
* Inventory management
* Receive stock
* Transfer stock
* Dispatch stock
* Stock movement history
* Low-stock alerts
* Redis-based caching or background processing
* Transaction-safe inventory operations
* Concurrent stock operation handling
* API validation and consistent error handling
* Unit and API/integration tests

## Requirements

* Backend: Laravel (PHP) (Version: 13.30.1)
* Database: PostgreSQL (Version: 16.15)
* Cache/Queue: Redis(Version: 7.4.11)
* API: RESTful JSON API
* Authentication: Sanctum
* Containerization: Docker Desktop
* Testing: Unit and API/integration tests

## Installation & Configuration

Clone the repository, install the backend dependencies, configure the environment, and start the required PostgreSQL and Redis services.

```bash
git clone git@github.com:supravatm/Laravel-PostgreSQL-Redis-WMS-API.git wms    # Clone the WMS repository
cp .env.example .env    # Create the environment configuration file
docker compose config    # Validate the Docker Compose configuration
docker compose up -d --build    # Build and start Docker containers
docker compose up -d    # Start Docker containers in detached mode
docker compose exec phpfpm bash    # Open a shell inside the PHP container
# composer create-project laravel/laravel . --prefer-dist    # Create the Laravel application
composer install    # Install project dependencies
php artisan key:generate    # Generate the Laravel application encryption key
php artisan migrate    # Run database migrations
# composer require laravel/sanctum    # Install Laravel Sanctum authentication
php artisan test --filter=ProductTest    # Run product-related tests
php artisan test --filter=WarehouseLocationTest    # Run warehouse location tests
php artisan test --filter=InventoryReadTest    # Run inventory read tests
php artisan tinker    # Open the Laravel interactive console
env('QUEUE_CONNECTION')    # Check the configured queue connection
php artisan queue:work redis    # Start the Redis queue worker
tail -f storage/logs/laravel.log    # Monitor Laravel application logs
docker compose exec postgres psql -U wms -d wms    # Connect to the PostgreSQL database
\dt    # List all PostgreSQL database tables
docker compose down    # Stop and remove Docker containers
```

## Usage

Start the application and use a REST API client such as Postman, Insomnia, Swagger UI, or cURL to authenticate and perform warehouse and inventory operations.


## API Documentation & Testing

<p align="center">
    <img align="center" src="https://raw.githubusercontent.com/supravatm/Laravel-PostgreSQL-Redis-WMS-API/main/public/api-docs-screen.png" alt="Preview" width="80%" />

</p>
</br>
<p style="font-weight: bold;">
Complete REST API Documentation can be found <a href="https://documenter.getpostman.com/view/497605/2sBYAvwWcG" target="_blank" rel="noopener noreferrer">here</a>
</p>

### Unit Tests

* Inventory quantity calculations
* Stock validation
* Low-stock threshold logic

## Contributing

Contributions should maintain the project's architectural principles, validation standards, transaction safety, test coverage, and code quality.

## License

MIT License

## Author

Supravat Mondal
