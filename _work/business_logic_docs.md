# Business Logic Documentation - Authentication System

## Project: Red Lane API
## Phase: 1 - Authentication API
## Date: 2025-11-24

---

## Overview

This document describes the business logic, rules, and workflows for the authentication system implemented in Phase 1. The system provides complete user authentication functionality using Laravel Sanctum with email verification and password reset capabilities.

---

## 1. User Registration

### Business Rule: Register New User

**Purpose:** Allow new users to create an account and receive email verification

**Location:** `App\Http\Controllers\Api\V1\Auth\AuthController@register`

**Inputs:**
- `email` (string, required, unique, valid email format)
- `password` (string, required, min 8 characters, confirmed)
- `first_name` (string, required, max 255 chars)
- `last_name` (string, required, max 255 chars)
- `phone` (string, optional, max 20 chars)

**Outputs:**
- HTTP 201 Created (success)
- HTTP 422 Unprocessable Entity (validation errors)

**Response Structure:**
```json
{
    "data": {
        "user": {
            "id": 1,
            "email": "user@example.com",
            "first_name": "John",
            "last_name": "Doe",
            "phone": "+1234567890",
            "email_verified_at": null,
            "created_at": "2025-11-24T00:00:00Z"
        },
        "token": "1|plaintext-token-here"
    },
    "message": "User registered successfully"
}
```

**Business Logic Flow:**
1. Validate all input fields via `RegisterRequest`
2. Check email uniqueness (database constraint)
3. Hash password using bcrypt (Laravel default)
4. Create user record with provided information
5. Trigger `Registered` event (sends verification email)
6. Generate Sanctum API token for immediate use
7. Return user data and token

**Invariants:**
- Email must be unique across all users (including soft-deleted)
- Password must be hashed before storage
- Email verification is optional for token generation but required for login
- Token is generated immediately upon registration

**Edge Cases:**
- Duplicate email: Returns 422 validation error
- Password too short: Returns 422 validation error
- Password mismatch: Returns 422 validation error
- Missing required fields: Returns 422 validation error
- Phone is optional: Can be null

**Dependencies:**
- `User` model
- `RegisterRequest` validation
- `UserResource` for response formatting
- Laravel's `Registered` event
- Email notification system

**Tests:**
- ✅ 11 registration tests covering all scenarios
- ✅ Email validation (required, format, unique)
- ✅ Password validation (length, confirmation)
- ✅ Name validation (first, last required)
- ✅ Phone optional validation
- ✅ Verification email sent

---

## 2. User Login

### Business Rule: Authenticate User

**Purpose:** Authenticate users and provide API access token

**Location:** `App\Http\Controllers\Api\V1\Auth\AuthController@login`

**Inputs:**
- `email` (string, required, valid email format)
- `password` (string, required)

**Outputs:**
- HTTP 200 OK (success)
- HTTP 401 Unauthorized (invalid credentials)
- HTTP 403 Forbidden (email not verified)
- HTTP 422 Unprocessable Entity (validation errors)
- HTTP 429 Too Many Requests (rate limit exceeded)

**Response Structure (Success):**
```json
{
    "data": {
        "user": {
            "id": 1,
            "email": "user@example.com",
            "first_name": "John",
            "last_name": "Doe",
            "phone": "+1234567890",
            "email_verified_at": "2025-11-24T00:00:00Z",
            "created_at": "2025-11-24T00:00:00Z"
        },
        "token": "2|plaintext-token-here"
    },
    "message": "Login successful"
}
```

**Business Logic Flow:**
1. Validate email and password via `LoginRequest`
2. Find user by email address
3. Verify password using Hash::check()
4. Check if email is verified (`hasVerifiedEmail()`)
5. Generate new Sanctum API token
6. Return user data and token

**Invariants:**
- User must exist in database
- Password must match hashed password
- Email must be verified (email_verified_at not null)
- Rate limit: 5 attempts per minute per IP
- Each login generates a new token (old tokens remain valid)

**Edge Cases:**
- Invalid email: Returns 401 "Invalid credentials"
- Invalid password: Returns 401 "Invalid credentials"
- Unverified email: Returns 403 "Email address is not verified"
- Soft-deleted user: Treated as non-existent (401)
- Rate limit exceeded: Returns 429 with Retry-After header

**Security Considerations:**
- Password never logged or exposed
- Generic error message for invalid credentials (no user enumeration)
- Rate limiting prevents brute force attacks
- Tokens stored hashed in database
- HTTPS required for token transmission

**Dependencies:**
- `User` model
- `LoginRequest` validation
- `UserResource` for response formatting
- Laravel Hash facade
- Sanctum token generation
- Rate limiter (configured in bootstrap/app.php)

**Tests:**
- ✅ 12 login tests covering all scenarios
- ✅ Valid credentials login
- ✅ Invalid email/password handling
- ✅ Email verification requirement
- ✅ Rate limiting (functional, not load tested)

---

## 3. User Logout

### Business Rule: Revoke Access Token

**Purpose:** Invalidate current API token and end session

**Location:** `App\Http\Controllers\Api\V1\Auth\AuthController@logout`

**Inputs:**
- Authentication required (Bearer token in header)

**Outputs:**
- HTTP 200 OK (success)
- HTTP 401 Unauthorized (not authenticated)

**Response Structure:**
```json
{
    "message": "Successfully logged out"
}
```

**Business Logic Flow:**
1. Verify authentication via Sanctum middleware
2. Get current access token from authenticated user
3. Delete token from database
4. Return success message

**Invariants:**
- Must be authenticated to logout
- Only current token is revoked (other tokens remain valid)
- Immediate token invalidation (no grace period)
- Idempotent operation (calling twice is safe)

**Edge Cases:**
- Already logged out: Returns 401 (token not found)
- Multiple devices: Only current device logged out
- Expired token: Returns 401 before reaching logout

**Dependencies:**
- Sanctum authentication middleware
- User model with HasApiTokens trait

**Tests:**
- ✅ Authenticated user can logout
- ✅ Logout requires authentication
- ✅ Token is revoked from database

---

## 4. Token Refresh

### Business Rule: Refresh Access Token

**Purpose:** Generate new token and revoke old one (token rotation)

**Location:** `App\Http\Controllers\Api\V1\Auth\AuthController@refresh`

**Inputs:**
- Authentication required (Bearer token in header)

**Outputs:**
- HTTP 200 OK (success)
- HTTP 401 Unauthorized (not authenticated)

**Response Structure:**
```json
{
    "data": {
        "token": "3|new-plaintext-token-here"
    },
    "message": "Token refreshed successfully"
}
```

**Business Logic Flow:**
1. Verify authentication via Sanctum middleware
2. Get current access token
3. Delete current token from database
4. Generate new Sanctum API token
5. Return new token

**Invariants:**
- Must be authenticated to refresh
- Old token is immediately revoked
- New token has fresh expiration (24 hours)
- Atomic operation (delete + create)

**Edge Cases:**
- Race condition: If two refreshes happen simultaneously, one will fail (401)
- Expired token: Returns 401 before refresh
- Network failure: Old token may be revoked but new token not received

**Security Considerations:**
- Token rotation reduces exposure window
- Old token cannot be reused
- New token uses different hash

**Dependencies:**
- Sanctum authentication middleware
- User model with HasApiTokens trait

**Tests:**
- ✅ Authenticated user can refresh token
- ✅ Refresh requires authentication
- ✅ New token is generated
- ✅ Old token is revoked (implicit in tests)

---

## 5. Get Current User

### Business Rule: Retrieve Authenticated User Details

**Purpose:** Get information about currently authenticated user

**Location:** `App\Http\Controllers\Api\V1\Auth\AuthController@me`

**Inputs:**
- Authentication required (Bearer token in header)

**Outputs:**
- HTTP 200 OK (success)
- HTTP 401 Unauthorized (not authenticated)

**Response Structure:**
```json
{
    "data": {
        "id": 1,
        "email": "user@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "phone": "+1234567890",
        "email_verified_at": "2025-11-24T00:00:00Z",
        "created_at": "2025-11-24T00:00:00Z"
    }
}
```

**Business Logic Flow:**
1. Verify authentication via Sanctum middleware
2. Get authenticated user from Auth facade
3. Format user data via UserResource
4. Return user data

**Invariants:**
- Must be authenticated
- User data is current (from database)
- Sensitive fields (password) are hidden
- Soft-deleted users cannot be retrieved (treated as 401)

**Edge Cases:**
- Expired token: Returns 401
- User deleted after authentication: Returns 401 on subsequent requests

**Dependencies:**
- Sanctum authentication middleware
- UserResource for response formatting
- Auth facade

**Tests:**
- ✅ Authenticated user can get details
- ✅ Requires authentication
- ✅ Correct user data returned

---

## 6. Forgot Password

### Business Rule: Request Password Reset

**Purpose:** Send password reset link to user's email

**Location:** `App\Http\Controllers\Api\V1\Auth\AuthController@forgotPassword`

**Inputs:**
- `email` (string, required, valid email format)

**Outputs:**
- HTTP 200 OK (always returns success for security)
- HTTP 422 Unprocessable Entity (validation errors)

**Response Structure:**
```json
{
    "message": "Password reset link sent to your email"
}
```

**Business Logic Flow:**
1. Validate email via `ForgotPasswordRequest`
2. Call Laravel's Password::sendResetLink()
3. If user exists, send password reset email
4. Always return success message (security)

**Invariants:**
- Always returns success (prevents user enumeration)
- Token stored hashed in password_reset_tokens table
- Token expires after configured time (Laravel default: 60 minutes)
- Previous reset tokens are overwritten

**Edge Cases:**
- Non-existent email: Returns success (security)
- Multiple requests: Overwrites previous token
- Email delivery failure: Not detected by API (async)
- Already reset password: Old token becomes invalid

**Security Considerations:**
- Generic success message prevents user enumeration
- Tokens are hashed before storage
- Tokens expire automatically
- Rate limiting recommended (not implemented in Phase 1)

**Dependencies:**
- `ForgotPasswordRequest` validation
- Laravel Password facade
- Email notification system
- password_reset_tokens table

**Tests:**
- ✅ Valid email sends reset link
- ✅ Email validation
- ✅ Non-existent email returns success (security)
- ✅ Notification sent to user

---

## 7. Reset Password

### Business Rule: Reset Password with Token

**Purpose:** Allow user to set new password using reset token

**Location:** `App\Http\Controllers\Api\V1\Auth\AuthController@resetPassword`

**Inputs:**
- `email` (string, required, valid email format)
- `token` (string, required)
- `password` (string, required, min 8 characters, confirmed)

**Outputs:**
- HTTP 200 OK (success)
- HTTP 400 Bad Request (invalid/expired token)
- HTTP 422 Unprocessable Entity (validation errors)

**Response Structure (Success):**
```json
{
    "message": "Password has been reset successfully"
}
```

**Response Structure (Invalid Token):**
```json
{
    "message": "Invalid or expired password reset token"
}
```

**Business Logic Flow:**
1. Validate inputs via `ResetPasswordRequest`
2. Call Laravel's Password::reset()
3. Verify token against database
4. If valid, hash new password and update user
5. Delete reset token from database
6. Return success/failure message

**Invariants:**
- Token must match database record
- Token must not be expired
- Email must match token record
- Password must meet minimum requirements
- Token is single-use (deleted after successful reset)

**Edge Cases:**
- Invalid token: Returns 400 error
- Expired token: Returns 400 error
- Email mismatch: Returns 400 error
- Password too weak: Returns 422 validation error
- Token already used: Returns 400 error

**Security Considerations:**
- Token is hashed in database
- Token expires after 60 minutes (configurable)
- Single-use tokens prevent replay attacks
- New password hashed with bcrypt
- Old sessions/tokens remain valid (manual logout recommended)

**Dependencies:**
- `ResetPasswordRequest` validation
- Laravel Password facade
- User model
- password_reset_tokens table

**Tests:**
- ✅ Valid token resets password
- ✅ Invalid token returns error
- ✅ Expired token returns error
- ✅ Validation on all fields
- ✅ Password confirmation required

---

## 8. Email Verification

### Business Rule: Verify User Email Address

**Purpose:** Confirm user owns the email address

**Location:** `App\Http\Controllers\Api\V1\Auth\AuthController@verifyEmail`

**Inputs:**
- Authentication required (Bearer token or guest with signed URL)
- `id` (user ID in URL)
- `hash` (email hash in URL)
- Signed URL (Laravel signature verification)

**Outputs:**
- HTTP 200 OK (success or already verified)
- HTTP 403 Forbidden (invalid signature)

**Response Structure:**
```json
{
    "message": "Email verified successfully"
}
```

**Business Logic Flow:**
1. Verify URL signature (Laravel signed middleware)
2. Find user by ID from URL
3. Check if already verified
4. Mark email as verified (`markEmailAsVerified()`)
5. Trigger `Verified` event
6. Return success message

**Invariants:**
- URL must be signed (prevents tampering)
- User must exist
- Email can be verified multiple times (idempotent)
- Verification is permanent (cannot be unverified)

**Edge Cases:**
- Already verified: Returns success (idempotent)
- Invalid signature: Returns 403
- User not found: Returns 404
- Expired link: Returns 403 (signature check)

**Security Considerations:**
- Signed URLs prevent unauthorized verification
- Hash includes user ID and email
- Links expire based on Laravel config
- Verification is one-way (cannot be undone)

**Dependencies:**
- Signed middleware
- User model with MustVerifyEmail
- Verified event

**Tests:**
- ✅ Email verification requirement in login
- ✅ Verification email sent on registration
- ✅ Idempotent verification (implicit)

---

## 9. Resend Verification Email

### Business Rule: Resend Verification Email

**Purpose:** Allow users to request new verification email

**Location:** `App\Http\Controllers\Api\V1\Auth\AuthController@resendVerification`

**Inputs:**
- Authentication required (Bearer token)

**Outputs:**
- HTTP 200 OK (success or already verified)
- HTTP 401 Unauthorized (not authenticated)

**Response Structure:**
```json
{
    "message": "Verification link sent"
}
```

**Business Logic Flow:**
1. Verify authentication
2. Check if email already verified
3. If not verified, send verification notification
4. Return success message

**Invariants:**
- Must be authenticated
- If already verified, returns success (idempotent)
- New email sent each time (for unverified users)

**Edge Cases:**
- Already verified: Returns success, no email sent
- Multiple requests: Each sends new email
- Email delivery failure: Not detected by API

**Dependencies:**
- Sanctum authentication
- User model with MustVerifyEmail
- Email notification system

**Tests:**
- ✅ Implicit in email verification flow
- ✅ Part of overall auth system

---

## Security Summary

### Authentication Security
- ✅ Bcrypt password hashing (cost factor: 10)
- ✅ API tokens hashed with SHA-256
- ✅ Password reset tokens hashed
- ✅ Token expiration: 24 hours (configurable)
- ✅ Rate limiting: 5 login attempts/minute per IP
- ✅ Email verification required for login

### API Security
- ✅ Sanctum middleware on protected routes
- ✅ Signed URLs for email verification
- ✅ Generic error messages (no user enumeration)
- ✅ Token rotation support (refresh endpoint)

### Data Protection
- ✅ Passwords never stored in plain text
- ✅ Tokens hashed before storage
- ✅ Soft deletes preserve audit trail
- ✅ Sensitive fields hidden in API responses

---

## Performance Considerations

### Database Queries
- Login: 1 query (user lookup by email)
- Registration: 2 queries (insert user, insert token)
- Logout: 1 query (delete token)
- Refresh: 2 queries (delete old token, insert new token)
- Me: 1 query (user lookup by token)

### Optimization
- Email indexed for fast lookup
- Token indexed for authentication
- No N+1 query issues
- Eager loading not needed (single table queries)

### Scalability
- Stateless authentication (no session storage)
- Tokens can be cached (not implemented in Phase 1)
- Rate limiting uses cache (Redis recommended)

---

## Change History

### Phase 1 - Initial Implementation (2025-11-24)
- ✅ Complete authentication system
- ✅ Email verification
- ✅ Password reset flow
- ✅ Token management
- ✅ Rate limiting
- ✅ 38 tests covering all scenarios

### Future Enhancements
- [ ] OAuth2 integration (Google, GitHub)
- [ ] Two-factor authentication (2FA)
- [ ] Login history tracking
- [ ] Device management
- [ ] Session management
- [ ] Password strength meter
- [ ] Account lockout after X failed attempts
