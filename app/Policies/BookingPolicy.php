<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }

    public function approve(User $user, Booking $booking): bool
    {
        return $this->canHandle($user, $booking);
    }

    public function reject(User $user, Booking $booking): bool
    {
        return $this->canHandle($user, $booking);
    }

    private function canHandle(User $user, Booking $booking): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $roleResourceMap = [
            'computer_lab_supervisor' => 'Lab',
            'library_supervisor' => 'Library',
            'venue_hall_supervisor' => 'Venue',
            'sports_equipment_supervisor' => 'Sports',
        ];

        foreach ($roleResourceMap as $role => $resourceType) {
            if ($user->hasRole($role)) {
                return $booking->resource->resource_type->name === $resourceType;
            }
        }

        return false;
    }
}
