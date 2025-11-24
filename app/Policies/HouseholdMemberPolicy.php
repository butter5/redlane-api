<?php

namespace App\Policies;

use App\Models\HouseholdMember;
use App\Models\User;

class HouseholdMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, HouseholdMember $householdMember): bool
    {
        return $user->id === $householdMember->address->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, HouseholdMember $householdMember): bool
    {
        return $user->id === $householdMember->address->user_id;
    }

    public function delete(User $user, HouseholdMember $householdMember): bool
    {
        return $user->id === $householdMember->address->user_id;
    }

    public function setPrimaryDeclarant(User $user, HouseholdMember $householdMember): bool
    {
        return $user->id === $householdMember->address->user_id;
    }
}
