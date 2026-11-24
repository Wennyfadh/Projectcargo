<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator() || $user->isCustomer();
    }

    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->isAdmin() || $user->isOperator()) {
            return true;
        }

        return $user->isCustomer() && $shipment->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    public function update(User $user, Shipment $shipment): bool
    {
        if ($user->isAdmin() || $user->isOperator()) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    public function restore(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin();
    }
}
