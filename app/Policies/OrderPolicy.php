<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'provider', 'trust_head']);
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole(['admin', 'trust_head'])) {
            return true;
        }

        if ($user->hasRole('provider')) {
            // Wait, provider user needs to be linked to vendor? 
            // Since we haven't linked user to vendor yet, we will just allow providers.
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasRole(['admin', 'provider']);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }
}
