# Impact Analysis - Phase 2: Database Changes

## Change Title
**Add Addresses and Household Members Tables**

---

## 1. Current Schema Overview

### Tables Affected
- **New Tables:**
  - `addresses` (NEW)
  - `household_members` (NEW)
- **Existing Tables to Modify:**
  - `users` (add relationships, no schema change)
  - `relationship_types` (already exists, no change)

### Columns Affected
**addresses table (NEW):**
- `id` - Primary key (BIGINT UNSIGNED)
- `user_id` - Foreign key to users (BIGINT UNSIGNED)
- `street_line_1` - VARCHAR(255), NOT NULL
- `street_line_2` - VARCHAR(255), NULLABLE
- `city` - VARCHAR(100), NOT NULL
- `state_province` - VARCHAR(100), NOT NULL
- `postal_code` - VARCHAR(20), NOT NULL
- `country_code` - CHAR(2), NOT NULL (ISO 3166-1 alpha-2)
- `is_primary` - BOOLEAN, DEFAULT false
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP, NULLABLE (soft deletes)

**household_members table (NEW):**
- `id` - Primary key (BIGINT UNSIGNED)
- `address_id` - Foreign key to addresses (BIGINT UNSIGNED)
- `first_name` - VARCHAR(255), NOT NULL
- `last_name` - VARCHAR(255), NOT NULL
- `date_of_birth` - DATE, NOT NULL
- `relationship_type_id` - Foreign key to relationship_types (BIGINT UNSIGNED)
- `is_primary_declarant` - BOOLEAN, DEFAULT false
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP, NULLABLE (soft deletes)

### Indexes/Keys Affected
**addresses:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (user_id, is_primary) - for finding primary address
- INDEX (user_id, deleted_at) - for soft delete queries
- INDEX (country_code) - for country filtering/validation

**household_members:**
- PRIMARY KEY (id)
- FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE CASCADE
- FOREIGN KEY (relationship_type_id) REFERENCES relationship_types(id)
- INDEX (address_id, is_primary_declarant) - for finding primary declarant
- INDEX (address_id, deleted_at) - for soft delete queries

### Relationships Impacted
**New Relationships:**
- User → Addresses (1:many)
- Address → HouseholdMembers (1:many)
- HouseholdMember → RelationshipType (many:1)
- User → HouseholdMembers (indirect through addresses)

---

## 2. Data Considerations

### Current Data Volume
- **users table:** Fresh installation, minimal test data
- **relationship_types table:** 5 seed records (spouse, child, parent, sibling, other)

### Sensitive Data Involved
- **addresses:** Physical location data (privacy concern)
- **household_members:** Personal data (names, DOB) - PII
- **is_primary flags:** Business critical data

### Risk of Data Loss/Corruption
- **LOW RISK:** Fresh table creation
- **MITIGATION:** All operations are additive (no data migration)
- **PROTECTION:** Soft deletes prevent accidental data loss

### Nullability/Default Values
- `street_line_2` - NULLABLE (not all addresses have line 2)
- `is_primary` - DEFAULT false (explicit flag required)
- `is_primary_declarant` - DEFAULT false (explicit flag required)
- All timestamps managed by Laravel

### Effect on Existing Records
- **NONE:** No existing address or household data to migrate
- **users table:** No schema changes, only relationship additions

---

## 3. Migration Plan

### Strategy
**Additive migrations only:**
1. Create addresses table
2. Create household_members table
3. Seed relationship_types if not already seeded
4. No data migration required (fresh tables)

### Step-by-Step Migration

#### Step 1: Create Addresses Table
```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('street_line_1');
    $table->string('street_line_2')->nullable();
    $table->string('city', 100);
    $table->string('state_province', 100);
    $table->string('postal_code', 20);
    $table->char('country_code', 2);
    $table->boolean('is_primary')->default(false);
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['user_id', 'is_primary']);
    $table->index(['user_id', 'deleted_at']);
    $table->index('country_code');
});
```

#### Step 2: Create Household Members Table
```php
Schema::create('household_members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('address_id')->constrained()->onDelete('cascade');
    $table->string('first_name');
    $table->string('last_name');
    $table->date('date_of_birth');
    $table->foreignId('relationship_type_id')->constrained('relationship_types');
    $table->boolean('is_primary_declarant')->default(false);
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['address_id', 'is_primary_declarant']);
    $table->index(['address_id', 'deleted_at']);
});
```

### Reversibility Plan
```php
// Down method for addresses:
Schema::dropIfExists('household_members'); // Drop first (has FK to addresses)
Schema::dropIfExists('addresses');
```

**Rollback safety:** 
- Clean rollback due to cascade deletes
- No orphaned records
- Order matters: household_members before addresses

---

## 4. Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Foreign key constraint violation | HIGH | LOW | Proper migration order, cascade deletes |
| Primary flag conflicts | MEDIUM | MEDIUM | Business logic validation, unique constraints |
| Orphaned household members | HIGH | LOW | ON DELETE CASCADE on address_id |
| Invalid country codes | MEDIUM | MEDIUM | Validation in Form Requests (ISO 3166-1) |
| Soft delete complexity | MEDIUM | LOW | Index on deleted_at, proper query scopes |
| Performance on large datasets | LOW | LOW | Proper indexing on foreign keys and flags |

---

## 5. Dependencies

### Code Modules
**New:**
- `App\Models\Address`
- `App\Models\HouseholdMember`
- `App\Http\Controllers\Api\V1\ProfileController`
- `App\Http\Controllers\Api\V1\AddressController`
- `App\Http\Controllers\Api\V1\HouseholdMemberController`
- `App\Http\Resources\AddressResource`
- `App\Http\Resources\HouseholdMemberResource`
- `App\Http\Resources\ProfileResource`
- `App\Policies\AddressPolicy`
- `App\Policies\HouseholdMemberPolicy`
- Form Requests (6 new classes)

**Modified:**
- `App\Models\User` (add relationships)
- `routes/api.php` (add new routes)

### External Systems
- None (internal API only)

### UI/Workflows
- Not applicable (API only)

---

## 6. Testing & Validation

### Unit Tests
- [ ] Address model relationship tests
- [ ] HouseholdMember model relationship tests
- [ ] Age calculation from date_of_birth
- [ ] Primary flag validation logic

### Integration Tests
- [ ] Migration runs successfully
- [ ] Foreign keys enforce referential integrity
- [ ] Soft deletes work correctly
- [ ] Cascade deletes work correctly

### E2E Validation
- [ ] Full CRUD operations on addresses
- [ ] Full CRUD operations on household members
- [ ] Profile management workflows
- [ ] Authorization tests (user isolation)
- [ ] Primary address/declarant business rules

### Manual Verification
```bash
# After migration:
php artisan migrate
php artisan db:show
php artisan tinker
>>> DB::table('addresses')->count()
>>> DB::table('household_members')->count()
>>> Schema::hasColumn('addresses', 'user_id')
>>> Schema::hasColumn('household_members', 'address_id')
```

---

## 7. Post-Change State

### New Schema Definition

**addresses:**
```sql
CREATE TABLE addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    street_line_1 VARCHAR(255) NOT NULL,
    street_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state_province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country_code CHAR(2) NOT NULL,
    is_primary BOOLEAN DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_primary (user_id, is_primary),
    INDEX idx_user_deleted (user_id, deleted_at),
    INDEX idx_country (country_code)
);
```

**household_members:**
```sql
CREATE TABLE household_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    address_id BIGINT UNSIGNED NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    relationship_type_id BIGINT UNSIGNED NOT NULL,
    is_primary_declarant BOOLEAN DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE CASCADE,
    FOREIGN KEY (relationship_type_id) REFERENCES relationship_types(id),
    INDEX idx_address_primary (address_id, is_primary_declarant),
    INDEX idx_address_deleted (address_id, deleted_at)
);
```

### Sample Data After Migration
```php
// No data initially - empty tables
// Seeds not required for reference data (relationship_types already seeded)
```

### Verified Compliance with 3.5NF

**First Normal Form (1NF):** ✅
- All columns contain atomic values
- No repeating groups
- Each column single-valued

**Second Normal Form (2NF):** ✅
- All non-key attributes fully dependent on primary key
- No partial dependencies

**Third Normal Form (3NF):** ✅
- No transitive dependencies
- Non-key attributes depend only on primary key

**Boyce-Codd Normal Form (3.5NF):** ✅
- Every determinant is a candidate key
- No anomalies in functional dependencies

### Verified Database is Not Messy
- ✅ Clear naming conventions
- ✅ Proper data types
- ✅ Appropriate indexes
- ✅ Foreign key constraints
- ✅ Soft deletes implemented
- ✅ No redundant data
- ✅ Type table pattern (relationship_types)

---

## Status
**READY FOR IMPLEMENTATION**

Next Steps:
1. Create migrations
2. Run migrations in test environment
3. Create models with relationships
4. Implement tests following TDD
5. Implement controllers and business logic
6. Validate all acceptance criteria

