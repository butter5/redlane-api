# Contributing Guidelines

Thank you for considering contributing to the Red Lane API project. This document outlines our development standards and workflow.

## Development Philosophy

This project follows strict **Test-Driven Development (TDD)** principles:

1. **Write tests first** before implementing any feature
2. **Run tests frequently** to ensure nothing breaks
3. **Maintain high test coverage**, especially for business logic
4. **All features must be complete** - no half-implemented features

## Getting Started

1. Fork the repository
2. Clone your fork
3. Follow setup instructions in [README.md](README.md)
4. Create a feature branch from `main`

```bash
git checkout -b feature/your-feature-name
```

## Code Standards

### Laravel Best Practices

- Follow Laravel 11 conventions and best practices
- Use Laravel's built-in features before adding external packages
- Keep controllers thin - move business logic to service classes
- Use form requests for validation
- Use API resources for transforming data

### Architecture

- **SOLID Principles** - Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion
- **Clean Architecture** - Separate concerns into layers
- **Dependency Injection** - Use constructor injection, not facades in business logic
- **Repository Pattern** - For complex data access logic

### Code Style

We use **Laravel Pint** to enforce PSR-12 coding standards.

Before committing:
```bash
composer format  # Auto-fix style issues
composer lint    # Check style without fixing
```

Style rules:
- Use type hints for all parameters and return types
- Use strict types: `declare(strict_types=1);`
- Write descriptive variable and method names
- Use early returns to reduce nesting
- Maximum line length: 120 characters
- Use trailing commas in arrays

### Testing

We use **Pest PHP** for testing.

#### Test Structure

```
tests/
├── Feature/          # API endpoint tests, integration tests
│   └── Api/         # Organize by API version if needed
├── Unit/            # Unit tests for individual classes
└── Pest.php         # Global test configuration
```

#### Writing Tests

**Feature Test Example:**
```php
<?php

test('user can create a declaration', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/declarations', [
            'type' => 'import',
            'value' => 1000,
        ]);
    
    $response
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'type',
                'value',
            ],
        ]);
    
    $this->assertDatabaseHas('declarations', [
        'user_id' => $user->id,
        'type' => 'import',
    ]);
});
```

**Unit Test Example:**
```php
<?php

use App\Services\DutyCalculator;

test('duty calculator calculates correct duty', function () {
    $calculator = new DutyCalculator();
    
    $result = $calculator->calculate(1000, 0.15);
    
    expect($result)->toBe(150.0);
});
```

#### Test Requirements

- All new features must have tests
- Aim for >80% code coverage
- Test happy paths and edge cases
- Test error conditions
- Use factories for test data
- Use descriptive test names

#### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test file
./vendor/bin/pest tests/Feature/DeclarationTest.php

# Run specific test
./vendor/bin/pest --filter="user can create a declaration"
```

## Database

### Migrations

- Use descriptive migration names
- Never modify existing migrations that have been deployed
- Always include both `up()` and `down()` methods
- Use foreign key constraints
- Add indexes for frequently queried columns

```bash
docker compose exec app php artisan make:migration create_declarations_table
```

### Database Design

- **3.5 Normal Form (BCNF)** - Eliminate redundancy
- **Use type tables** instead of enums for extensibility
- **No SQLite in production** - Use MySQL 8.0
- **UTF-8 encoding** - Use `utf8mb4` charset
- **Timestamps** - Include `created_at` and `updated_at`
- **Soft deletes** - Use for auditing where appropriate

### Seeders

Create seeders for:
- Initial roles and permissions
- Test data for development
- Reference data (countries, currencies, etc.)

```bash
docker compose exec app php artisan make:seeder RolesAndPermissionsSeeder
```

## API Development

### API Routes

Place API routes in `routes/api.php`. All routes are automatically prefixed with `/api`.

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('declarations', DeclarationController::class);
});
```

### Controllers

Keep controllers thin:

```php
class DeclarationController extends Controller
{
    public function __construct(
        private DeclarationService $service
    ) {}

    public function store(StoreDeclarationRequest $request)
    {
        $declaration = $this->service->create($request->validated());
        
        return new DeclarationResource($declaration);
    }
}
```

### Requests

Use Form Requests for validation:

```php
class StoreDeclarationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or check permissions
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:import,export'],
            'value' => ['required', 'numeric', 'min:0'],
        ];
    }
}
```

### Resources

Use API Resources for transforming data:

```php
class DeclarationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'value' => $this->value,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

### API Documentation

Use Scribe annotations:

```php
/**
 * Create a declaration
 * 
 * Creates a new customs declaration.
 * 
 * @group Declarations
 * @authenticated
 * 
 * @bodyParam type string required The declaration type. Example: import
 * @bodyParam value number required The declaration value. Example: 1000
 * 
 * @response 201 scenario="Success" {"data": {"id": 1, "type": "import"}}
 */
public function store(StoreDeclarationRequest $request)
{
    // ...
}
```

## Git Workflow

### Branch Naming

- `feature/description` - New features
- `fix/description` - Bug fixes
- `refactor/description` - Code refactoring
- `docs/description` - Documentation updates
- `test/description` - Test additions/updates

### Commit Messages

Follow conventional commits:

```
type(scope): subject

body

footer
```

Types:
- `feat` - New feature
- `fix` - Bug fix
- `refactor` - Code refactoring
- `test` - Test updates
- `docs` - Documentation
- `chore` - Maintenance

Example:
```
feat(declarations): add import declaration endpoint

- Add DeclarationController
- Add DeclarationService
- Add validation and tests
- Update API documentation

Closes #123
```

### Pull Request Process

1. **Ensure tests pass**: `composer test`
2. **Check code style**: `composer lint`
3. **Update documentation** if needed
4. **Write descriptive PR description**
5. **Link related issues**
6. **Request review** from team members
7. **Address review comments**
8. **Squash commits** if requested

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tests added/updated
- [ ] All tests passing
- [ ] Code style checked

## Checklist
- [ ] Code follows project style
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No breaking changes (or documented)
```

## Code Review Guidelines

When reviewing code:

- Check for test coverage
- Verify code follows standards
- Look for security issues
- Check for performance implications
- Ensure documentation is updated
- Be constructive and respectful

## Security

- Never commit secrets or credentials
- Use environment variables for configuration
- Validate all user input
- Use parameterized queries (Eloquent does this)
- Hash passwords (Laravel does this)
- Use HTTPS in production
- Keep dependencies updated

### Reporting Security Issues

Email security@redlane.local (or appropriate contact) with:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

## Performance

- Use eager loading to avoid N+1 queries
- Cache expensive operations
- Use database indexes appropriately
- Use queues for time-consuming tasks
- Monitor query performance

## Questions?

- Check existing documentation
- Ask in project chat/discussions
- Open an issue for bugs
- Contact maintainers for guidance

## License

By contributing, you agree that your contributions will be licensed under the same license as the project.
