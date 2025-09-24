<?php
namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function viewClient(User $user): bool
    {
        return $user->hasRole('client');
    }
}
