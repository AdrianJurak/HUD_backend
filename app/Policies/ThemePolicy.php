<?php

namespace App\Policies;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ThemePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Theme $theme): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Theme $theme): bool
    {
        return $user->id === $theme->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Theme $theme): bool
    {
        return $user->id === $theme->user_id;
    }
}
