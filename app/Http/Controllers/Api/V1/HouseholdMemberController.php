<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdMember\StoreHouseholdMemberRequest;
use App\Http\Requests\HouseholdMember\UpdateHouseholdMemberRequest;
use App\Http\Resources\HouseholdMemberResource;
use App\Models\Address;
use App\Models\HouseholdMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class HouseholdMemberController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $addressIds = $user->addresses()->pluck('id');
        $members = HouseholdMember::whereIn('address_id', $addressIds)->with('relationshipType')->get();

        return response()->json([
            'data' => HouseholdMemberResource::collection($members),
        ]);
    }

    public function store(StoreHouseholdMemberRequest $request): JsonResponse
    {
        $address = Address::findOrFail($request->address_id);
        Gate::authorize('view', $address);

        $member = $address->householdMembers()->create($request->validated());
        $member->load('relationshipType');

        return response()->json([
            'data' => new HouseholdMemberResource($member),
            'message' => 'Household member created successfully',
        ], 201);
    }

    public function show(HouseholdMember $householdMember): JsonResponse
    {
        Gate::authorize('view', $householdMember);

        $householdMember->load('relationshipType');

        return response()->json([
            'data' => new HouseholdMemberResource($householdMember),
        ]);
    }

    public function update(UpdateHouseholdMemberRequest $request, HouseholdMember $householdMember): JsonResponse
    {
        Gate::authorize('update', $householdMember);

        $address = Address::findOrFail($request->address_id);
        Gate::authorize('view', $address);

        $householdMember->update($request->validated());
        $householdMember->load('relationshipType');

        return response()->json([
            'data' => new HouseholdMemberResource($householdMember->fresh(['relationshipType'])),
            'message' => 'Household member updated successfully',
        ]);
    }

    public function destroy(HouseholdMember $householdMember): JsonResponse
    {
        Gate::authorize('delete', $householdMember);

        $householdMember->delete();

        return response()->json([
            'message' => 'Household member deleted successfully',
        ]);
    }

    public function setPrimaryDeclarant(HouseholdMember $householdMember): JsonResponse
    {
        Gate::authorize('setPrimaryDeclarant', $householdMember);

        HouseholdMember::where('address_id', $householdMember->address_id)
            ->update(['is_primary_declarant' => false]);

        $householdMember->update(['is_primary_declarant' => true]);
        $householdMember->load('relationshipType');

        return response()->json([
            'data' => new HouseholdMemberResource($householdMember->fresh(['relationshipType'])),
            'message' => 'Primary declarant set successfully',
        ]);
    }
}
