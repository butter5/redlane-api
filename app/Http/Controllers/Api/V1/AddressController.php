<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    public function index(): JsonResponse
    {
        $addresses = Auth::user()->addresses()->get();

        return response()->json([
            'data' => AddressResource::collection($addresses),
        ]);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $user = Auth::user();

        $isPrimary = $user->addresses()->count() === 0;

        $address = $user->addresses()->create([
            ...$request->validated(),
            'is_primary' => $isPrimary,
        ]);

        return response()->json([
            'data' => new AddressResource($address),
            'message' => 'Address created successfully',
        ], 201);
    }

    public function show(Address $address): JsonResponse
    {
        Gate::authorize('view', $address);

        return response()->json([
            'data' => new AddressResource($address),
        ]);
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        Gate::authorize('update', $address);

        $address->update($request->validated());

        return response()->json([
            'data' => new AddressResource($address->fresh()),
            'message' => 'Address updated successfully',
        ]);
    }

    public function destroy(Address $address): JsonResponse
    {
        Gate::authorize('delete', $address);

        if ($address->is_primary && $address->householdMembers()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete primary address with household members',
            ], 422);
        }

        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully',
        ]);
    }

    public function setPrimary(Address $address): JsonResponse
    {
        Gate::authorize('setPrimary', $address);

        $user = Auth::user();

        $user->addresses()->update(['is_primary' => false]);

        $address->update(['is_primary' => true]);

        return response()->json([
            'data' => new AddressResource($address->fresh()),
            'message' => 'Primary address set successfully',
        ]);
    }
}
