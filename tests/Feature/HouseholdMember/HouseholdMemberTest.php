<?php

use App\Models\Address;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    DB::table('relationship_types')->insert([
        ['code' => 'spouse', 'description' => 'Spouse', 'created_at' => now(), 'updated_at' => now()],
        ['code' => 'child', 'description' => 'Child', 'created_at' => now(), 'updated_at' => now()],
    ]);
});

test('authenticated user can create household member', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'spouse')->first();

    $response = $this->actingAs($user)->postJson('/api/v1/household-members', [
        'address_id' => $address->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'date_of_birth' => '1990-05-15',
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'date_of_birth',
                'age',
                'relationship_type',
                'is_primary_declarant',
                'created_at',
            ],
        ]);

    $this->assertDatabaseHas('household_members', [
        'address_id' => $address->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
    ]);
});

test('household member age is calculated correctly from date of birth', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'child')->first();

    $dateOfBirth = now()->subYears(10)->format('Y-m-d');

    $response = $this->actingAs($user)->postJson('/api/v1/household-members', [
        'address_id' => $address->id,
        'first_name' => 'Child',
        'last_name' => 'Doe',
        'date_of_birth' => $dateOfBirth,
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'age' => 10,
            ],
        ]);
});

test('authenticated user can list household members', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'child')->first();

    HouseholdMember::factory()->count(3)->create([
        'address_id' => $address->id,
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/household-members');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('authenticated user can get specific household member', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'spouse')->first();
    $member = HouseholdMember::factory()->create([
        'address_id' => $address->id,
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response = $this->actingAs($user)->getJson("/api/v1/household-members/{$member->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $member->id,
                'first_name' => $member->first_name,
            ],
        ]);
});

test('user cannot access another users household member', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $otherUser->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'child')->first();
    $member = HouseholdMember::factory()->create([
        'address_id' => $address->id,
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response = $this->actingAs($user)->getJson("/api/v1/household-members/{$member->id}");

    $response->assertStatus(403);
});

test('authenticated user can update household member', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'spouse')->first();
    $member = HouseholdMember::factory()->create([
        'address_id' => $address->id,
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response = $this->actingAs($user)->putJson("/api/v1/household-members/{$member->id}", [
        'address_id' => $address->id,
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'date_of_birth' => '1985-01-01',
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'first_name' => 'Updated',
                'last_name' => 'Name',
            ],
        ]);
});

test('authenticated user can delete household member', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'child')->first();
    $member = HouseholdMember::factory()->create([
        'address_id' => $address->id,
        'relationship_type_id' => $relationshipType->id,
        'is_primary_declarant' => false,
    ]);

    $response = $this->actingAs($user)->deleteJson("/api/v1/household-members/{$member->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('household_members', [
        'id' => $member->id,
    ]);
});

test('authenticated user can set household member as primary declarant', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'spouse')->first();
    $member1 = HouseholdMember::factory()->create([
        'address_id' => $address->id,
        'relationship_type_id' => $relationshipType->id,
        'is_primary_declarant' => true,
    ]);
    $member2 = HouseholdMember::factory()->create([
        'address_id' => $address->id,
        'relationship_type_id' => $relationshipType->id,
        'is_primary_declarant' => false,
    ]);

    $response = $this->actingAs($user)->postJson("/api/v1/household-members/{$member2->id}/set-primary-declarant");

    $response->assertStatus(200);

    $member1->refresh();
    $member2->refresh();

    expect($member1->is_primary_declarant)->toBeFalse();
    expect($member2->is_primary_declarant)->toBeTrue();
});

test('only one household member can be primary declarant', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'spouse')->first();
    $member1 = HouseholdMember::factory()->create([
        'address_id' => $address->id,
        'relationship_type_id' => $relationshipType->id,
        'is_primary_declarant' => true,
    ]);
    $member2 = HouseholdMember::factory()->create([
        'address_id' => $address->id,
        'relationship_type_id' => $relationshipType->id,
        'is_primary_declarant' => false,
    ]);

    $this->actingAs($user)->postJson("/api/v1/household-members/{$member2->id}/set-primary-declarant");

    $primaryCount = HouseholdMember::where('address_id', $address->id)
        ->where('is_primary_declarant', true)
        ->count();

    expect($primaryCount)->toBe(1);
});

test('household member creation requires first name', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'child')->first();

    $response = $this->actingAs($user)->postJson('/api/v1/household-members', [
        'address_id' => $address->id,
        'last_name' => 'Doe',
        'date_of_birth' => '1990-01-01',
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name']);
});

test('household member creation requires valid date of birth', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $relationshipType = DB::table('relationship_types')->where('code', 'child')->first();

    $response = $this->actingAs($user)->postJson('/api/v1/household-members', [
        'address_id' => $address->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'date_of_birth' => 'invalid-date',
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date_of_birth']);
});

test('household member creation requires valid relationship type', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/api/v1/household-members', [
        'address_id' => $address->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'date_of_birth' => '1990-01-01',
        'relationship_type_id' => 999999,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['relationship_type_id']);
});

test('unauthenticated user cannot create household member', function () {
    $address = Address::factory()->create();
    $relationshipType = DB::table('relationship_types')->where('code', 'child')->first();

    $response = $this->postJson('/api/v1/household-members', [
        'address_id' => $address->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'date_of_birth' => '1990-01-01',
        'relationship_type_id' => $relationshipType->id,
    ]);

    $response->assertStatus(401);
});
