<?php

namespace App\Actions;

use App\Models\User;

class LogoutUserAction
{
    public function execute(User $user): void
    {
        $user->token()?->revoke();
    }
}
