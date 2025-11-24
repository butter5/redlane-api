# Feature Flag System Documentation

## Overview

The Red Lane API implements a feature flag system using Laravel Pennant with database persistence. This allows for gradual feature rollout, A/B testing, and feature toggles at both global and user-specific levels.

## Architecture

### Components

1. **Features Class** (`app/Features.php`)
   - Defines all available feature flags
   - Specifies default states
   - Registered in AppServiceProvider

2. **FeatureFlagService** (`app/Services/FeatureFlagService.php`)
   - Business logic for managing flags
   - Handles global and user-specific operations
   - Implements cascade logic: user overrides > global > defaults

3. **Controllers**
   - `FeatureFlagController`: User-facing endpoints
   - `Admin/FeatureFlagController`: Administrative management

4. **Middleware** (`CheckFeatureFlag`)
   - Route-level feature gating
   - Usage: `Route::middleware('feature:flag_name')`

## Available Feature Flags

| Flag                         | Default | Description                      |
|------------------------------|---------|----------------------------------|
| `ocr_processing`             | OFF     | Enable receipt OCR processing    |
| `multi_leg_trips`            | OFF     | Allow multi-leg trip creation    |
| `admin_dashboard`            | ON      | Administrative controls          |
| `declaration_export`         | OFF     | PDF export of declarations       |
| `currency_api_integration`   | OFF     | Auto-fetch exchange rates        |

## API Endpoints

### User Endpoints (Authenticated)

#### Get Feature Flags
```http
GET /api/v1/feature-flags
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "flags": {
      "ocr_processing": false,
      "multi_leg_trips": false,
      "admin_dashboard": true,
      "declaration_export": false,
      "currency_api_integration": false
    }
  }
}
```

### Admin Endpoints (Admin Only)

#### List All Flags with Statistics
```http
GET /api/v1/admin/feature-flags
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "flags": {
      "ocr_processing": {
        "global": false,
        "user_overrides": 3
      },
      "admin_dashboard": {
        "global": true,
        "user_overrides": 0
      }
    }
  }
}
```

#### Toggle Flag Globally
```http
POST /api/v1/admin/feature-flags/{key}/toggle
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "flag": "ocr_processing",
    "enabled": true
  },
  "message": "Feature flag toggled successfully"
}
```

#### Enable Flag for Specific User
```http
POST /api/v1/admin/feature-flags/{key}/users/{userId}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "flag": "ocr_processing",
    "user_id": 123,
    "enabled": true
  },
  "message": "Feature flag enabled for user"
}
```

#### Disable Flag for Specific User
```http
DELETE /api/v1/admin/feature-flags/{key}/users/{userId}
Authorization: Bearer {token}
```

## Usage Examples

### Using in Routes

```php
Route::middleware(['auth:sanctum', 'feature:ocr_processing'])
    ->post('/receipts/ocr', [ReceiptController::class, 'processOcr']);
```

### Using in Controllers

```php
use App\Services\FeatureFlagService;

class ReceiptController extends Controller
{
    public function __construct(
        protected FeatureFlagService $featureFlagService
    ) {}

    public function store(Request $request)
    {
        $user = $request->user();
        
        if ($this->featureFlagService->isActive('ocr_processing', $user)) {
            // OCR is enabled for this user
            return $this->processWithOcr($request);
        }
        
        return $this->processManually($request);
    }
}
```

### Using in Blade Views

```php
@if(Feature::active('admin_dashboard'))
    <a href="/admin">Admin Dashboard</a>
@endif
```

## Feature Flag Behavior

### Cascade Logic

1. **User-Specific Override**: If a user has an explicit flag setting, use it
2. **Global Setting**: If no user override exists, use the global setting
3. **Default Value**: If neither exists, use the default from Features::defaults()

### Example Scenarios

**Scenario 1: Global Enable**
```php
// Enable globally
$service->globalEnable('ocr_processing');

// All users will see it as enabled (unless they have a user-specific override)
$service->isActive('ocr_processing', $user); // true for all users
```

**Scenario 2: User-Specific Override**
```php
// Enable globally
$service->globalEnable('ocr_processing');

// Disable for specific user
$service->disableForUser('ocr_processing', $specificUser);

// Most users see it enabled, but specific user sees it disabled
$service->isActive('ocr_processing', $regularUser);    // true
$service->isActive('ocr_processing', $specificUser);   // false
```

## Setup & Installation

### 1. Run Migrations

```bash
php artisan migrate
```

This creates the `features` table for storing flag states.

### 2. Seed Initial Flags

```bash
php artisan db:seed --class=FeatureFlagSeeder
```

This sets up all flags with their default states.

### 3. Environment Configuration

Add to `.env`:
```env
PENNANT_STORE=database
```

## Adding New Feature Flags

1. **Update Features Class**

```php
// app/Features.php
public static function defaults(): array
{
    return [
        // ... existing flags
        'new_feature' => false,
    ];
}
```

2. **Seed the New Flag**

```bash
php artisan db:seed --class=FeatureFlagSeeder
```

3. **Use the Flag**

The flag is immediately available via the service, controllers, and middleware.

## Testing

Run the feature flag test suite:

```bash
# All tests
./vendor/bin/pest

# Feature flag tests only
./vendor/bin/pest tests/Feature/FeatureFlags/
./vendor/bin/pest tests/Unit/Services/FeatureFlagServiceTest.php
```

## Performance Considerations

- Feature flag states are cached by Pennant
- Database queries are minimized through caching
- Cascade logic checks user overrides first to avoid unnecessary queries
- Consider using Redis cache for high-traffic applications

## Best Practices

1. **Default to OFF**: New features should default to OFF for safety
2. **Test Thoroughly**: Always test both enabled and disabled states
3. **Clean Up**: Remove flags once features are fully rolled out
4. **Document**: Update this file when adding new flags
5. **Monitor**: Track flag usage and user overrides via admin panel

## Frontend Integration

Frontend applications should:

1. Fetch flags on user login:
```javascript
const response = await fetch('/api/v1/feature-flags', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const { data } = await response.json();
// Store in Pinia/Vuex: data.flags
```

2. Check flags before rendering features:
```javascript
if (flags.ocr_processing) {
  // Show OCR button
}
```

3. Handle flag changes gracefully (user should re-login or app should refresh flags periodically)

## Troubleshooting

### Flag not taking effect

1. Clear cache: `php artisan cache:clear`
2. Check database: `SELECT * FROM features WHERE name = 'flag_name';`
3. Verify middleware is applied to route
4. Check user-specific overrides

### Tests failing

1. Ensure migrations are run in test environment
2. Use RefreshDatabase trait in tests
3. Check test database configuration

## Security Notes

- Admin endpoints should be protected with proper authorization
- Feature flag changes should be audited/logged
- User-specific overrides should be used cautiously for PII reasons
- Consider rate limiting on admin endpoints

## Support

For issues or questions about the feature flag system, contact the development team or refer to the Laravel Pennant documentation: https://laravel.com/docs/pennant
