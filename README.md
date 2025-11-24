# Red Lane API

Laravel 11 RESTful API for customs duty declaration management system.

## Overview

The Red Lane API is a modern backend system built with Laravel 11, providing comprehensive API endpoints for managing customs duty declarations. It features robust authentication, role-based permissions, feature flags, and comprehensive API documentation.

## Technology Stack

- **Framework:** Laravel 11.x
- **PHP:** 8.2+ (PHP 8.3 recommended)
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis
- **Web Server:** Nginx
- **Containerization:** Docker & Docker Compose

### Core Packages

- **Authentication:** Laravel Sanctum (API token authentication)
- **Permissions:** Spatie Laravel Permission (roles & permissions)
- **Feature Flags:** Laravel Pennant
- **API Documentation:** Scribe
- **Testing:** Pest PHP
- **Code Style:** Laravel Pint

## Prerequisites

- Docker Desktop or Docker Engine (20.10+)
- Docker Compose (2.0+)
- Git

## Local Development Setup

### 1. Clone the Repository

```bash
git clone https://github.com/butter5/redlane-api.git
cd redlane-api
```

### 2. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

The `.env.example` file contains comprehensive documentation for each configuration option. Key settings for Docker development:

- `DB_HOST=mysql` (Docker service name)
- `REDIS_HOST=redis` (Docker service name)
- `MAIL_HOST=mailhog` (Docker service name)

### 3. Start Docker Services

Build and start all services:

```bash
docker compose up -d --build
```

This will start the following services:
- **app** (PHP 8.2-FPM) - The Laravel application
- **nginx** - Web server (http://localhost:8000)
- **mysql** - MySQL 8.0 database (port 3307 on host)
- **redis** - Redis cache and queue (port 6380 on host)
- **mailhog** - Email testing UI (http://localhost:8025)

### 4. Install Dependencies

```bash
docker compose exec app composer install
```

### 5. Generate Application Key

```bash
docker compose exec app php artisan key:generate
```

### 6. Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

### 7. Publish Package Configurations

Publish Sanctum, Spatie Permissions, and Pennant configurations:

```bash
docker compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
docker compose exec app php artisan vendor:publish --provider="Laravel\Pennant\PennantServiceProvider"
```

### 8. Access the Application

- **API:** http://localhost:8000
- **Mailhog UI:** http://localhost:8025 (email testing)
- **API Documentation:** http://localhost:8000/docs (after generating)

## Development Workflow

### Running Tests

Execute the full test suite with Pest:

```bash
docker compose exec app ./vendor/bin/pest
```

Run specific test files:

```bash
docker compose exec app ./vendor/bin/pest tests/Feature/ExampleTest.php
```

Run tests with coverage:

```bash
docker compose exec app ./vendor/bin/pest --coverage
```

### Code Style

Check code style with Laravel Pint:

```bash
docker compose exec app ./vendor/bin/pint --test
```

Automatically fix code style issues:

```bash
docker compose exec app ./vendor/bin/pint
```

### API Documentation

Generate API documentation with Scribe:

```bash
docker compose exec app php artisan scribe:generate
```

The documentation will be available at http://localhost:8000/docs

### Artisan Commands

Run any Laravel Artisan command:

```bash
docker compose exec app php artisan [command]
```

Common commands:
```bash
# Database operations
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan db:seed

# Cache management
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear

# Queue management
docker compose exec app php artisan queue:work

# Tinker (REPL)
docker compose exec app php artisan tinker
```

### Database Access

Connect to MySQL:

```bash
docker compose exec mysql mysql -u laravel -p
# Password: laravel_password
```

### Logs

View application logs:

```bash
docker compose exec app tail -f storage/logs/laravel.log
```

View container logs:

```bash
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f mysql
```

## Project Structure

```
.
├── app/                    # Application code
│   ├── Http/
│   │   ├── Controllers/   # API controllers
│   │   ├── Middleware/    # Custom middleware
│   │   └── Resources/     # API resources
│   ├── Models/            # Eloquent models
│   └── Providers/         # Service providers
├── bootstrap/             # Framework bootstrap
├── config/                # Configuration files
├── database/
│   ├── factories/         # Model factories
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── docker/                # Docker configuration
│   ├── nginx/             # Nginx config
│   └── mysql/             # MySQL config
├── public/                # Web server document root
├── resources/             # Views and assets
├── routes/                # Application routes
│   ├── api.php           # API routes
│   └── web.php           # Web routes
├── storage/               # Compiled files, logs
├── tests/                 # Pest PHP tests
│   ├── Feature/          # Feature tests
│   └── Unit/             # Unit tests
├── _work/                 # Development documentation
├── .env.example           # Environment configuration template
├── docker-compose.yml     # Docker services configuration
├── Dockerfile             # PHP-FPM container definition
└── README.md             # This file
```

## Testing Guidelines

This project follows Test-Driven Development (TDD) principles:

1. **Write tests first** before implementing features
2. **Run tests frequently** during development
3. **Maintain high coverage** especially for business logic
4. **Write E2E tests** for all API endpoints

Test structure:
- `tests/Unit/` - Unit tests for individual classes/methods
- `tests/Feature/` - Integration tests for API endpoints

## Code Style Guidelines

- Follow Laravel 11 best practices
- Use PSR-12 coding standards (enforced by Laravel Pint)
- Write descriptive method and variable names
- Add PHPDoc blocks for complex logic
- Keep controllers thin, use service classes for business logic

## Docker Commands Reference

```bash
# Start services
docker compose up -d

# Stop services
docker compose down

# Rebuild containers
docker compose up -d --build

# View running containers
docker compose ps

# Execute commands in containers
docker compose exec app [command]
docker compose exec mysql [command]
docker compose exec redis redis-cli

# Remove all containers and volumes (⚠️ destroys data)
docker compose down -v
```

## Environment-Specific Notes

### Local Development
- Debug mode is enabled
- All emails are captured by Mailhog
- Redis is used for cache and queues
- Database exposed on host port 3307

### Production Deployment
- Set `APP_DEBUG=false`
- Configure real SMTP settings
- Use strong database passwords
- Enable HTTPS
- Configure proper backup strategy
- Set appropriate cache TTLs

## Troubleshooting

### Port Already in Use

If ports 8000, 3307, or 6380 are already in use, update the port mappings in `docker-compose.yml` or stop the conflicting service.

### Permission Issues

If you encounter permission errors:

```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Database Connection Failed

Ensure MySQL is fully started:

```bash
docker compose ps
docker compose logs mysql
```

Wait for the message: "mysqld: ready for connections"

### Clear All Caches

```bash
docker compose exec app php artisan optimize:clear
```

## API Authentication

This API uses Laravel Sanctum for token-based authentication:

1. Register/login to receive an API token
2. Include token in requests: `Authorization: Bearer {token}`
3. Tokens are hashed in the database for security

Example:
```bash
curl -H "Authorization: Bearer your-token-here" \
     http://localhost:8000/api/user
```

## Contributing

1. Create a feature branch from `main`
2. Write tests first (TDD)
3. Implement the feature
4. Ensure all tests pass
5. Run Laravel Pint to fix code style
6. Submit a pull request

## License

This project is proprietary software. All rights reserved.

## Support

For questions or issues, please contact the development team or open an issue in the repository.
