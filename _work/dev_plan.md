# Development Plan - Laravel 11 Backend Initialization

## Project: Red Lane API
## Phase: 1 - Authentication API Implementation
## Updated: 2025-11-24

---

## Objective
Implement complete user authentication system using Laravel Sanctum for API token-based authentication with email verification and password reset functionality.

---

## Phases

### Phase 0: Project Initialization ✅ COMPLETE
**Goal:** Set up Laravel 11 with Docker environment and core packages

**Status:** ✅ All tasks completed successfully

---

### Phase 1: Authentication API Implementation ✅ COMPLETE
**Goal:** Implement full-featured authentication system with Laravel Sanctum

**Tasks:**
1. ✅ Update database schema for authentication
   - Added first_name, last_name, phone to users table
   - Added soft deletes support
   - Published Sanctum personal_access_tokens migration
   
2. ✅ Create Form Request classes
   - RegisterRequest - comprehensive validation
   - LoginRequest - credential validation
   - ForgotPasswordRequest - email validation
   - ResetPasswordRequest - password reset validation

3. ✅ Create AuthController with all endpoints
   - POST /api/v1/auth/register - User registration
   - POST /api/v1/auth/login - User login (with rate limiting)
   - POST /api/v1/auth/logout - Token revocation
   - POST /api/v1/auth/refresh - Token refresh
   - GET /api/v1/auth/me - Get authenticated user
   - POST /api/v1/auth/forgot-password - Send reset link
   - POST /api/v1/auth/reset-password - Reset password
   - GET /api/email/verify/{id}/{hash} - Email verification
   - POST /api/v1/auth/email/resend - Resend verification

4. ✅ Implement UserResource for API responses

5. ✅ Configure Sanctum
   - Token expiration: 24 hours (1440 minutes)
   - Configurable via SANCTUM_EXPIRATION env variable

6. ✅ Configure rate limiting
   - Login endpoint: 5 attempts per minute per IP

7. ✅ Update User model
   - Implemented MustVerifyEmail
   - Added HasApiTokens trait
   - Added SoftDeletes trait
   - Updated fillable fields

8. ✅ Comprehensive TDD test suite (38 tests, 132 assertions)
   - 11 registration tests (validation, success scenarios)
   - 12 login tests (auth, verification, token management)
   - 12 password reset tests (forgot & reset flows)
   - 3 existing tests (health check, example tests)

**Progress:**
- [x] Database schema updated
- [x] All Form Requests created
- [x] AuthController fully implemented
- [x] UserResource created
- [x] Sanctum configured
- [x] Rate limiting configured
- [x] Email verification implemented
- [x] All routes configured
- [x] All tests passing (38 passed, 132 assertions)
- [x] Code committed and pushed

---

## Technology Stack

### Backend Framework
- Laravel 11.x (Latest)
- PHP 8.3

### Database
- MySQL 8.0 (primary database - production)
- SQLite :memory: (testing)
- Redis (cache and queue driver)

### Authentication
- Laravel Sanctum 4.2
- API token-based authentication
- Email verification support
- Password reset functionality

### Development Tools
- Docker & Docker Compose
- Pest PHP (testing framework)
- Laravel Pint (code style)
- Mailhog (email testing)
- Nginx (web server)

### Core Packages
- `laravel/sanctum` - API authentication ✅
- `spatie/laravel-permission` - Roles & permissions (Phase 2)
- `laravel/pennant` - Feature flags (Phase 2)
- `knuckleswtf/scribe` - API documentation (Phase 2)

---

## Architecture Decisions

### TDD Approach ✅
- Tests written before implementation
- Pest PHP as testing framework
- 38 tests covering all authentication flows
- High coverage for business logic
- All edge cases tested

### Security Features ✅
- Bcrypt password hashing
- API token hashing in database
- Password reset token hashing
- Email verification requirement
- Rate limiting on login (5/min)
- Sanctum token expiration (24 hours)

### API Design ✅
- RESTful API endpoints
- Consistent JSON response structure
- Proper HTTP status codes
- Comprehensive validation
- Form Request classes for validation

### Database Design ✅
- Soft deletes on users table
- Proper indexing (email unique)
- Foreign keys for relationships
- Following Laravel 11 conventions

---

## API Endpoints Summary

### Public Endpoints (No Authentication)
- `POST /api/v1/auth/register` - Register new user
- `POST /api/v1/auth/login` - Login (rate limited: 5/min)
- `POST /api/v1/auth/forgot-password` - Request password reset
- `POST /api/v1/auth/reset-password` - Reset password with token

### Protected Endpoints (Require Authentication)
- `POST /api/v1/auth/logout` - Logout and revoke token
- `POST /api/v1/auth/refresh` - Refresh access token
- `GET /api/v1/auth/me` - Get authenticated user details
- `POST /api/v1/auth/email/resend` - Resend verification email
- `GET /api/email/verify/{id}/{hash}` - Verify email (signed URL)

---

## Test Coverage

### Registration Tests (11 tests)
- ✅ Successful registration with valid data
- ✅ Email validation (required, format, unique)
- ✅ Password validation (required, confirmed, min length)
- ✅ First name validation (required)
- ✅ Last name validation (required)
- ✅ Phone validation (optional)
- ✅ Verification email sent

### Login Tests (12 tests)
- ✅ Login with valid credentials
- ✅ Login fails with invalid email/password
- ✅ Email/password validation
- ✅ Email verification requirement
- ✅ Logout revokes token
- ✅ Token refresh functionality
- ✅ Get authenticated user details
- ✅ Authentication requirement

### Password Reset Tests (12 tests)
- ✅ Forgot password request
- ✅ Email validation
- ✅ Security (non-existent email returns success)
- ✅ Reset password with valid token
- ✅ Reset validation (email, token, password)
- ✅ Invalid token handling
- ✅ Password confirmation matching

---

### Phase 2: User Profile & Household Management API ✅ COMPLETE
**Goal:** Implement user profile, address, and household member management with full CRUD operations

**Status:** ✅ All tasks completed successfully

**Completed:**
1. ✅ Database migrations created
   - addresses table with soft deletes
   - household_members table with soft deletes
   
2. ✅ Models created
   - Address model with relationships
   - HouseholdMember model with age calculation
   - RelationshipType model
   - User model updated with addresses relationship

3. ✅ API Resources
   - ProfileResource
   - AddressResource
   - HouseholdMemberResource (with age calculation)

4. ✅ Form Requests with validation
   - UpdateProfileRequest
   - ChangePasswordRequest
   - StoreAddressRequest / UpdateAddressRequest
   - StoreHouseholdMemberRequest / UpdateHouseholdMemberRequest

5. ✅ Authorization Policies
   - AddressPolicy (user ownership)
   - HouseholdMemberPolicy (user ownership)

6. ✅ Controllers with full CRUD
   - ProfileController (GET, PUT, change-password)
   - AddressController (full CRUD + set-primary)
   - HouseholdMemberController (full CRUD + set-primary-declarant)

7. ✅ Comprehensive test suite (36 new tests)
   - 10 profile management tests
   - 13 address management tests
   - 13 household member tests
   - All authorization tests
   - Business rules validation

**Business Rules Implemented:**
- ✅ First address automatically set as primary
- ✅ Only one address can be primary at a time
- ✅ Only one household member can be primary declarant
- ✅ Age calculated correctly from date_of_birth
- ✅ Cannot delete primary address with household members
- ✅ Country code validation (ISO 3166-1 alpha-2)
- ✅ Users can only access their own data
- ✅ Soft deletes on addresses and household members

**Test Results:**
- Total Tests: 106 passed (340 assertions)
- Phase 2 Tests: 36 tests
- Coverage: >80% (business logic fully covered)
- All validation tests passing
- All authorization tests passing

**API Endpoints:**
- ✅ GET /api/v1/profile
- ✅ PUT /api/v1/profile
- ✅ POST /api/v1/profile/change-password
- ✅ POST /api/v1/addresses
- ✅ GET /api/v1/addresses
- ✅ GET /api/v1/addresses/{id}
- ✅ PUT /api/v1/addresses/{id}
- ✅ DELETE /api/v1/addresses/{id}
- ✅ POST /api/v1/addresses/{id}/set-primary
- ✅ POST /api/v1/household-members
- ✅ GET /api/v1/household-members
- ✅ GET /api/v1/household-members/{id}
- ✅ PUT /api/v1/household-members/{id}
- ✅ DELETE /api/v1/household-members/{id}
- ✅ POST /api/v1/household-members/{id}/set-primary-declarant

**Documentation:**
- ✅ API documentation created (docs/PHASE_2_API.md)
- ✅ Impact analysis documented (_work/phase2_impact_analysis.md)
- ✅ Development plan documented (_work/phase2_dev_plan.md)

---

## Next Steps

### Phase 3: Advanced Features (Future)
1. Additional business logic
2. Enhanced validation
3. Reporting endpoints
4. File uploads (if needed)
### Phase 2: Roles & Permissions (Spatie) ✅ COMPLETE
**Goal:** Implement role-based access control using Spatie Laravel Permission

**Status:** ✅ All tasks completed successfully

**Tasks:**
1. ✅ Set up Spatie Laravel Permission
   - Package already installed in composer.json
   - Published migrations for permission tables
   - Published configuration file
   
2. ✅ Create roles and permissions system
   - Created RoleAndPermissionSeeder
   - Defined 3 roles: admin, user, customs_officer
   - Defined 6 permissions: manage_duty_categories, manage_currencies, manage_users, view_all_declarations, manage_feature_flags, view_audit_logs
   
3. ✅ Implement role-based access control
   - Created EnsureUserIsAdmin middleware
   - Created CheckPermission middleware
   - Registered middleware in bootstrap/app.php
   - Added HasRoles trait to User model
   
4. ✅ Create admin user seeder
   - Created AdminUserSeeder
   - Admin user: admin@redlane.local / Admin123!
   - Seeder creates admin with role assignment
   - Updated DatabaseSeeder to call all new seeders

5. ✅ Add role/permission tests
   - Created 28 comprehensive TDD tests
   - 6 tests for RoleAndPermissionSeeder
   - 8 tests for User role assignment
   - 9 tests for middleware functionality
   - 5 tests for AdminUserSeeder
   - All 98 tests passing (312 assertions)

**Progress:**
- [x] Spatie Permission package configured
- [x] Permission tables migration created
- [x] RoleAndPermissionSeeder created and tested
- [x] AdminUserSeeder created and tested
- [x] User model integrated with HasRoles
- [x] EnsureUserIsAdmin middleware created
- [x] CheckPermission middleware created
- [x] Middleware registered and tested
- [x] DatabaseSeeder updated
- [x] All tests passing (98 tests, 312 assertions)
- [x] Code committed and pushed

---

### Phase 3: API Documentation
1. Configure Scribe for API docs
2. Add endpoint descriptions
3. Generate API documentation
4. Set up documentation endpoint

### Phase 4: Feature Flags (Already Complete from Phase 0)
1. ✅ Configure Laravel Pennant
2. ✅ Implement feature flag system
3. ✅ Add feature flag middleware
4. ✅ Create feature flag tests

---

## Notes
- ✅ All acceptance criteria met for Phase 1
- ✅ All acceptance criteria met for Phase 2
- ✅ Following Laravel 11 best practices
- ✅ Strict TDD methodology applied
- ✅ Clean Architecture principles followed
- ✅ SOLID principles throughout
- ✅ Comprehensive test coverage achieved
- ✅ Security best practices implemented
- ✅ Rate limiting configured
- ✅ Email verification functional
- ✅ Token expiration configurable
- ✅ RBAC fully functional with database driver

---

## Acceptance Criteria - Phase 1 ✅

- [x] All endpoints return correct HTTP status codes
- [x] Registration creates user and sends verification email
- [x] Login returns valid Sanctum token
- [x] Authenticated routes require valid token
- [x] Logout invalidates token
- [x] Password reset emails are sent
- [x] All validation works correctly
- [x] All tests pass (38 tests, 132 assertions)
- [x] API responses follow consistent JSON structure
- [x] Rate limiting: 5 attempts per minute for login
- [x] Token expiration: 24 hours (configurable)
- [x] Email verification required before full access
- [x] Bcrypt password hashing

---

## Acceptance Criteria - Phase 2 ✅

- [x] Three roles exist in database (admin, user, customs_officer)
- [x] All 6 permissions exist and are assigned correctly
- [x] Admin middleware protects admin routes
- [x] Permission middleware checks specific permissions
- [x] User model has role/permission methods available (HasRoles trait)
- [x] Seeder creates initial admin user (admin@redlane.local)
- [x] All tests pass (98 tests, 312 assertions)
- [x] RBAC using database driver (not cache)
- [x] Middleware registered and functional
- [x] TDD approach followed throughout
