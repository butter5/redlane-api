<?php

namespace App\Policies;

use App\Models\DutyCategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DutyCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Public can view duty categories
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DutyCategory $dutyCategory): bool
    {
        return true; // Public can view duty categories
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_duty_categories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DutyCategory $dutyCategory): bool
    {
        return $user->hasPermissionTo('manage_duty_categories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DutyCategory $dutyCategory): bool
    {
        return $user->hasPermissionTo('manage_duty_categories');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DutyCategory $dutyCategory): bool
    {
        return $user->hasPermissionTo('manage_duty_categories');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DutyCategory $dutyCategory): bool
    {
        return $user->hasPermissionTo('manage_duty_categories');
    }
}
