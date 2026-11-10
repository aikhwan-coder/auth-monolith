<?php

namespace Modules\Shared\Concerns;

use App\Models\User;

trait ChecksAdmin
{
    protected function isAdmin(?User $user): bool
    {
        return $user !== null
            && $user->roles()->where('slug', 'admin')->exists();
    }
}
