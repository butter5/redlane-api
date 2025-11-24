# Setup Guide

This guide provides step-by-step instructions for setting up the Red Lane API after cloning the repository.

## Quick Start (Docker)

```bash
# 1. Clone and navigate
git clone https://github.com/butter5/redlane-api.git
cd redlane-api

# 2. Copy environment file
cp .env.example .env

# 3. Start Docker services
docker compose up -d --build

# 4. Install dependencies
docker compose exec app composer install

# 5. Generate application key
docker compose exec app php artisan key:generate

# 6. Run migrations
docker compose exec app php artisan migrate

# 7. Publish package configurations (optional but recommended)
docker compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
docker compose exec app php artisan vendor:publish --provider="Laravel\Pennant\PennantServiceProvider"

# 8. Access the application
# API: http://localhost:8000
# Mailhog: http://localhost:8025
```

## Package Configuration Details

### Laravel Sanctum (API Authentication)

Sanctum provides token-based authentication for APIs.

**Publish configuration:**
```bash
docker compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

**Published files:**
- `config/sanctum.php` - Sanctum configuration
- Migration for `personal_access_tokens` table

**Key features:**
- API token authentication
- SPA authentication
- Mobile app authentication
- Token abilities/scopes

**Configuration notes:**
- Set `SANCTUM_STATEFUL_DOMAINS` in .env for SPA authentication
- Tokens are hashed in database for security
- Default expiration: no expiration (configure as needed)

### Spatie Laravel Permission (RBAC)

Provides role and permission management.

**Publish configuration:**
```bash
docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

**Published files:**
- `config/permission.php` - Permission system configuration
- Migrations for roles and permissions tables

**Key features:**
- Role-based access control
- Permission-based access control
- Guard-specific permissions
- Caching for performance

**Usage:**
```php
// Assign role
$user->assignRole('admin');

// Check permission
$user->hasPermissionTo('edit articles');

// Direct permission
$user->givePermissionTo('edit articles');
```

**Configuration notes:**
- Supports multiple guards (web, api)
- Cached for performance
- Clear cache: `php artisan permission:cache-reset`

### Laravel Pennant (Feature Flags)

Feature flag management system.

**Publish configuration:**
```bash
docker compose exec app php artisan vendor:publish --provider="Laravel\Pennant\PennantServiceProvider"
```

**Published files:**
- `config/pennant.php` - Feature flag configuration
- Migration for `features` table

**Key features:**
- Toggle features on/off
- User-specific features
- Gradual rollouts
- A/B testing support

**Usage:**
```php
// Check feature
if (Feature::active('new-dashboard')) {
    // New dashboard code
}

// Activate for user
Feature::activate('new-dashboard', $user);
```

**Configuration notes:**
- Database driver by default
- Supports scoped features
- Can use custom resolvers

### Scribe (API Documentation)

Automatic API documentation generation.

**Publish configuration:**
```bash
docker compose exec app php artisan vendor:publish --tag=scribe-config
```

**Generate documentation:**
```bash
docker compose exec app php artisan scribe:generate
```

**Access documentation:**
- http://localhost:8000/docs

**Key features:**
- Auto-generate from routes
- Postman collection export
- Try-it-out interface
- Customizable themes

**Configuration notes:**
- Configure in `config/scribe.php`
- Use docblocks for better documentation
- Supports response examples

## Database Configuration

### Run Migrations

```bash
# Run all migrations
docker compose exec app php artisan migrate

# Fresh migration (WARNING: destroys data)
docker compose exec app php artisan migrate:fresh

# With seeding
docker compose exec app php artisan migrate:fresh --seed
```

### Create Seeders

```bash
docker compose exec app php artisan make:seeder RolesAndPermissionsSeeder
```

## Testing

```bash
# Run all tests
docker compose exec app composer test

# Run with coverage
docker compose exec app composer test:coverage

# Run specific test
docker compose exec app ./vendor/bin/pest tests/Feature/AuthTest.php
```

## Code Quality

```bash
# Check code style
docker compose exec app composer lint

# Fix code style
docker compose exec app composer format
```

## Common Tasks

### Create a Controller
```bash
docker compose exec app php artisan make:controller Api/UserController --api
```

### Create a Model
```bash
docker compose exec app php artisan make:model Declaration -mfsc
# -m: migration, -f: factory, -s: seeder, -c: controller
```

### Create a Migration
```bash
docker compose exec app php artisan make:migration create_declarations_table
```

### Create a Request
```bash
docker compose exec app php artisan make:request StoreDeclarationRequest
```

### Create a Resource
```bash
docker compose exec app php artisan make:resource DeclarationResource
```

### Clear Caches
```bash
docker compose exec app php artisan optimize:clear
```

## Troubleshooting

### Permission Errors

```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues

```bash
# Check MySQL is running
docker compose ps

# View MySQL logs
docker compose logs mysql

# Restart MySQL
docker compose restart mysql
```

### Clear All Application Caches

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

## Production Deployment Notes

Before deploying to production:

1. **Environment:**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Generate strong `APP_KEY`

2. **Database:**
   - Use strong passwords
   - Enable SSL connections
   - Regular backups

3. **Cache:**
   - Use Redis for cache and sessions
   - Configure cache TTLs appropriately

4. **Optimization:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   ```

5. **Queue Workers:**
   - Use Supervisor or similar for queue workers
   - Monitor worker health

6. **Security:**
   - Enable HTTPS
   - Configure CORS properly
   - Set appropriate `SANCTUM_STATEFUL_DOMAINS`
   - Regular security updates

## Additional Resources

- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Sanctum Documentation](https://laravel.com/docs/11.x/sanctum)
- [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)
- [Pennant Documentation](https://laravel.com/docs/11.x/pennant)
- [Scribe Documentation](https://scribe.knuckles.wtf/laravel/)
- [Pest Documentation](https://pestphp.com/)
- [Pint Documentation](https://laravel.com/docs/11.x/pint)
