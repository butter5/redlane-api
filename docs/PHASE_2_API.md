# Phase 2 API Documentation - User Profile & Household Management

## Profile Management

### Get User Profile
**GET** `/api/v1/profile`

**Headers:**
- `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+1234567890",
    "email_verified_at": "2025-11-24T00:00:00+00:00",
    "created_at": "2025-11-24T00:00:00+00:00"
  }
}
```

---

### Update User Profile
**PUT** `/api/v1/profile`

**Headers:**
- `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "first_name": "Jane",
  "last_name": "Smith",
  "phone": "+0987654321"
}
```

**Validation Rules:**
- `first_name`: required, string, max 255
- `last_name`: required, string, max 255
- `phone`: optional, string, max 20

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "Jane",
    "last_name": "Smith",
    "phone": "+0987654321",
    "email_verified_at": "2025-11-24T00:00:00+00:00",
    "created_at": "2025-11-24T00:00:00+00:00"
  },
  "message": "Profile updated successfully"
}
```

---

### Change Password
**POST** `/api/v1/profile/change-password`

**Headers:**
- `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "current_password": "OldPassword123!",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

**Validation Rules:**
- `current_password`: required, must match current password
- `password`: required, confirmed, min 8 characters
- `password_confirmation`: required, must match password

**Response (200):**
```json
{
  "message": "Password changed successfully"
}
```

---

## Address Management

### List User Addresses
**GET** `/api/v1/addresses`

**Headers:**
- `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "street_line_1": "123 Main St",
      "street_line_2": "Apt 4B",
      "city": "New York",
      "state_province": "NY",
      "postal_code": "10001",
      "country_code": "US",
      "is_primary": true,
      "created_at": "2025-11-24T00:00:00+00:00",
      "updated_at": "2025-11-24T00:00:00+00:00"
    }
  ]
}
```

---

### Create Address
**POST** `/api/v1/addresses`

**Headers:**
- `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "street_line_1": "123 Main St",
  "street_line_2": "Apt 4B",
  "city": "New York",
  "state_province": "NY",
  "postal_code": "10001",
  "country_code": "US"
}
```

**Validation Rules:**
- `street_line_1`: required, string, max 255
- `street_line_2`: optional, string, max 255
- `city`: required, string, max 100
- `state_province`: required, string, max 100
- `postal_code`: required, string, max 20
- `country_code`: required, 2-letter ISO 3166-1 alpha-2 code

**Business Rules:**
- First address for a user is automatically set as primary

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "street_line_1": "123 Main St",
    "street_line_2": "Apt 4B",
    "city": "New York",
    "state_province": "NY",
    "postal_code": "10001",
    "country_code": "US",
    "is_primary": true,
    "created_at": "2025-11-24T00:00:00+00:00",
    "updated_at": "2025-11-24T00:00:00+00:00"
  },
  "message": "Address created successfully"
}
```

---

### Get Specific Address
**GET** `/api/v1/addresses/{id}`

**Headers:**
- `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "street_line_1": "123 Main St",
    "street_line_2": "Apt 4B",
    "city": "New York",
    "state_province": "NY",
    "postal_code": "10001",
    "country_code": "US",
    "is_primary": true,
    "created_at": "2025-11-24T00:00:00+00:00",
    "updated_at": "2025-11-24T00:00:00+00:00"
  }
}
```

**Error (403):**
- Returns 403 if address belongs to another user

---

### Update Address
**PUT** `/api/v1/addresses/{id}`

**Headers:**
- `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "street_line_1": "456 Oak Ave",
  "city": "Boston",
  "state_province": "MA",
  "postal_code": "02101",
  "country_code": "US"
}
```

**Validation Rules:** Same as Create Address

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "street_line_1": "456 Oak Ave",
    "city": "Boston",
    "state_province": "MA",
    "postal_code": "02101",
    "country_code": "US",
    "is_primary": true,
    "created_at": "2025-11-24T00:00:00+00:00",
    "updated_at": "2025-11-24T00:00:00+00:00"
  },
  "message": "Address updated successfully"
}
```

---

### Delete Address (Soft Delete)
**DELETE** `/api/v1/addresses/{id}`

**Headers:**
- `Authorization: Bearer {token}`

**Business Rules:**
- Cannot delete primary address if it has household members

**Response (200):**
```json
{
  "message": "Address deleted successfully"
}
```

**Error (422):**
```json
{
  "message": "Cannot delete primary address with household members"
}
```

---

### Set Primary Address
**POST** `/api/v1/addresses/{id}/set-primary`

**Headers:**
- `Authorization: Bearer {token}`

**Business Rules:**
- Only one address can be primary at a time
- Previous primary address is automatically unset

**Response (200):**
```json
{
  "data": {
    "id": 2,
    "street_line_1": "456 Oak Ave",
    "city": "Boston",
    "state_province": "MA",
    "postal_code": "02101",
    "country_code": "US",
    "is_primary": true,
    "created_at": "2025-11-24T00:00:00+00:00",
    "updated_at": "2025-11-24T00:00:00+00:00"
  },
  "message": "Primary address set successfully"
}
```

---

## Household Member Management

### List Household Members
**GET** `/api/v1/household-members`

**Headers:**
- `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "first_name": "Jane",
      "last_name": "Doe",
      "date_of_birth": "1990-05-15",
      "age": 34,
      "relationship_type": {
        "id": 1,
        "code": "spouse",
        "description": "Spouse"
      },
      "is_primary_declarant": true,
      "created_at": "2025-11-24T00:00:00+00:00",
      "updated_at": "2025-11-24T00:00:00+00:00"
    }
  ]
}
```

---

### Create Household Member
**POST** `/api/v1/household-members`

**Headers:**
- `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "address_id": 1,
  "first_name": "Jane",
  "last_name": "Doe",
  "date_of_birth": "1990-05-15",
  "relationship_type_id": 1
}
```

**Validation Rules:**
- `address_id`: required, must exist and belong to authenticated user
- `first_name`: required, string, max 255
- `last_name`: required, string, max 255
- `date_of_birth`: required, format YYYY-MM-DD
- `relationship_type_id`: required, must exist in relationship_types table

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "first_name": "Jane",
    "last_name": "Doe",
    "date_of_birth": "1990-05-15",
    "age": 34,
    "relationship_type": {
      "id": 1,
      "code": "spouse",
      "description": "Spouse"
    },
    "is_primary_declarant": false,
    "created_at": "2025-11-24T00:00:00+00:00",
    "updated_at": "2025-11-24T00:00:00+00:00"
  },
  "message": "Household member created successfully"
}
```

---

### Get Specific Household Member
**GET** `/api/v1/household-members/{id}`

**Headers:**
- `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "first_name": "Jane",
    "last_name": "Doe",
    "date_of_birth": "1990-05-15",
    "age": 34,
    "relationship_type": {
      "id": 1,
      "code": "spouse",
      "description": "Spouse"
    },
    "is_primary_declarant": true,
    "created_at": "2025-11-24T00:00:00+00:00",
    "updated_at": "2025-11-24T00:00:00+00:00"
  }
}
```

**Error (403):**
- Returns 403 if household member belongs to another user

---

### Update Household Member
**PUT** `/api/v1/household-members/{id}`

**Headers:**
- `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "address_id": 1,
  "first_name": "Updated",
  "last_name": "Name",
  "date_of_birth": "1985-01-01",
  "relationship_type_id": 1
}
```

**Validation Rules:** Same as Create Household Member

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "first_name": "Updated",
    "last_name": "Name",
    "date_of_birth": "1985-01-01",
    "age": 39,
    "relationship_type": {
      "id": 1,
      "code": "spouse",
      "description": "Spouse"
    },
    "is_primary_declarant": true,
    "created_at": "2025-11-24T00:00:00+00:00",
    "updated_at": "2025-11-24T00:00:00+00:00"
  },
  "message": "Household member updated successfully"
}
```

---

### Delete Household Member (Soft Delete)
**DELETE** `/api/v1/household-members/{id}`

**Headers:**
- `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "message": "Household member deleted successfully"
}
```

---

### Set Primary Declarant
**POST** `/api/v1/household-members/{id}/set-primary-declarant`

**Headers:**
- `Authorization: Bearer {token}`

**Business Rules:**
- Only one household member per address can be primary declarant
- Previous primary declarant is automatically unset

**Response (200):**
```json
{
  "data": {
    "id": 2,
    "first_name": "Jane",
    "last_name": "Doe",
    "date_of_birth": "1990-05-15",
    "age": 34,
    "relationship_type": {
      "id": 1,
      "code": "spouse",
      "description": "Spouse"
    },
    "is_primary_declarant": true,
    "created_at": "2025-11-24T00:00:00+00:00",
    "updated_at": "2025-11-24T00:00:00+00:00"
  },
  "message": "Primary declarant set successfully"
}
```

---

## Available Relationship Types

The following relationship types are seeded in the database:

- `spouse` - Spouse
- `child` - Child
- `parent` - Parent
- `sibling` - Sibling
- `other` - Other

---

## Authorization Rules

All endpoints require authentication via Sanctum token.

**Address Access:**
- Users can only view, create, update, or delete their own addresses
- Attempting to access another user's address returns 403 Forbidden

**Household Member Access:**
- Users can only view, create, update, or delete household members for their own addresses
- Attempting to access another user's household member returns 403 Forbidden

---

## Business Rules Summary

1. **First Address is Primary:** The first address created by a user is automatically set as primary
2. **Single Primary Address:** Only one address can be primary at a time
3. **Single Primary Declarant:** Only one household member per address can be primary declarant
4. **Age Calculation:** Age is automatically calculated from date_of_birth
5. **Cannot Delete Primary Address with Members:** Primary address cannot be deleted if it has household members
6. **Country Code Validation:** Country codes must be 2-letter ISO 3166-1 alpha-2 codes
7. **Soft Deletes:** Both addresses and household members use soft deletes

---

## Test Coverage

**Total Tests:** 106 (340 assertions)

**Profile Management:** 10 tests
- Get profile
- Update profile
- Change password
- Validation tests

**Address Management:** 13 tests
- CRUD operations
- Primary address logic
- Authorization
- Validation tests

**Household Member Management:** 13 tests
- CRUD operations
- Primary declarant logic
- Age calculation
- Authorization
- Validation tests

**Phase 1 Tests:** 70 tests (authentication, feature flags, etc.)

All tests passing ✅
