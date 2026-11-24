<?php

namespace App\Policies;

use App\Models\ShipmentException;
use App\Models\User;

class ShipmentExceptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator() || $user->isCustomer();
    }

    public function view(User $user, ShipmentException $shipmentException): bool
    {
        if ($user->isAdmin() || $user->isOperator()) {
            return true;
        }

        return $user->isCustomer() && $shipmentException->shipment?->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    public function update(User $user, ShipmentException $shipmentException): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    public function delete(User $user, ShipmentException $shipmentException): bool
    {
        return $user->isAdmin();
    }
}
