# Development Plan - Phase 2: User Profile & Household Management API

## Project: Red Lane API
## Phase: 2 - User Profile & Household Management
## Started: 2025-11-24
## Status: IN PROGRESS

---

## Objective
Implement user profile management, address management, and household member management APIs with full CRUD operations following TDD principles.

---

## Current Phase: Phase 2 Implementation

### Tasks Breakdown

#### 1. Documentation & Planning
- [x] Analyze existing codebase structure
- [x] Review Phase 1 implementation patterns
- [ ] Create impact_analysis.md for database changes
- [ ] Update database_integrity_audit.md

#### 2. Database Migrations
- [ ] Create migration: create_addresses_table
  - Fields: id, user_id (FK), street_line_1, street_line_2, city, state_province, postal_code, country_code, is_primary, created_at, updated_at, deleted_at
- [ ] Create migration: create_household_members_table
  - Fields: id, address_id (FK), first_name, last_name, date_of_birth, relationship_type_id (FK), is_primary_declarant, created_at, updated_at, deleted_at

#### 3. Models (TDD: Tests First)
- [ ] Test: Address model relationships
- [ ] Create Address model
- [ ] Test: HouseholdMember model relationships
- [ ] Create HouseholdMember model
- [ ] Test: User model relationships
- [ ] Update User model with relationships

#### 4. API Resources
- [ ] Test: ProfileResource format
- [ ] Create ProfileResource
- [ ] Test: AddressResource format
- [ ] Create AddressResource
- [ ] Test: HouseholdMemberResource format (with age calculation)
- [ ] Create HouseholdMemberResource

#### 5. Form Requests (Validation)
- [ ] Test: UpdateProfileRequest validation
- [ ] Create UpdateProfileRequest
- [ ] Test: ChangePasswordRequest validation
- [ ] Create ChangePasswordRequest
- [ ] Test: StoreAddressRequest validation
- [ ] Create StoreAddressRequest
- [ ] Test: UpdateAddressRequest validation
- [ ] Create UpdateAddressRequest
- [ ] Test: StoreHouseholdMemberRequest validation
- [ ] Create StoreHouseholdMemberRequest
- [ ] Test: UpdateHouseholdMemberRequest validation
- [ ] Create UpdateHouseholdMemberRequest

#### 6. Policies (Authorization)
- [ ] Test: AddressPolicy - user can only access own addresses
- [ ] Create AddressPolicy
- [ ] Test: HouseholdMemberPolicy - user can only access own household
- [ ] Create HouseholdMemberPolicy

#### 7. Controllers & Endpoints (TDD)
##### Profile Endpoints
- [ ] Test: GET /api/v1/profile
- [ ] Test: PUT /api/v1/profile
- [ ] Test: POST /api/v1/profile/change-password
- [ ] Create ProfileController

##### Address Endpoints
- [ ] Test: POST /api/v1/addresses
- [ ] Test: GET /api/v1/addresses
- [ ] Test: GET /api/v1/addresses/{id}
- [ ] Test: PUT /api/v1/addresses/{id}
- [ ] Test: DELETE /api/v1/addresses/{id} (soft delete)
- [ ] Test: POST /api/v1/addresses/{id}/set-primary
- [ ] Create AddressController

##### Household Member Endpoints
- [ ] Test: POST /api/v1/household-members
- [ ] Test: GET /api/v1/household-members
- [ ] Test: GET /api/v1/household-members/{id}
- [ ] Test: PUT /api/v1/household-members/{id}
- [ ] Test: DELETE /api/v1/household-members/{id}
- [ ] Test: POST /api/v1/household-members/{id}/set-primary-declarant
- [ ] Create HouseholdMemberController

#### 8. Business Rules Tests
- [ ] Test: User must have at least one address
- [ ] Test: Only one address can be primary
- [ ] Test: Only one household member can be primary declarant
- [ ] Test: Age calculation from date_of_birth
- [ ] Test: Cannot delete primary address if it has household members
- [ ] Test: Validate country codes (ISO 3166-1 alpha-2)
- [ ] Test: Cannot access other user's addresses
- [ ] Test: Cannot access other user's household members

#### 9. Integration & Validation
- [ ] Run all tests (target: >80% coverage)
- [ ] Verify all endpoints with manual testing
- [ ] Code review
- [ ] Security scan
- [ ] Update API documentation

---

## Technology Stack (Inherited from Phase 1)
- Laravel 11.x
- PHP 8.3
- MySQL 8.0 (production)
- SQLite :memory: (testing)
- Laravel Sanctum (authentication)
- Pest PHP (testing)

---

## Architecture Principles
1. **TDD**: Write tests before implementation
2. **SOLID**: Maintain separation of concerns
3. **Clean Architecture**: Data access, business logic, presentation layers
4. **3.5NF**: Database normalized to 3.5 Normal Form
5. **Type Tables**: Use relationship_types table (already exists)

---

## API Design Patterns (from Phase 1)
- RESTful endpoints
- Consistent JSON response structure
- Proper HTTP status codes
- Form Request validation
- Resource transformers
- Policy-based authorization

---

## Acceptance Criteria
- [ ] All endpoints functional and tested
- [ ] User can only access their own data
- [ ] Primary address/declarant logic enforced
- [ ] Age calculated correctly from DOB
- [ ] Validation prevents invalid data
- [ ] All tests pass with >80% coverage
- [ ] No security vulnerabilities
- [ ] API documentation updated

---

## Progress Tracking
**Started:** 2025-11-24
**Current Status:** Planning Complete - Ready for Implementation
**Next Step:** Create database impact analysis

