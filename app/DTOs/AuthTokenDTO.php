<?php

namespace App\DTOs;

use App\Models\User;
use Laravel\Passport\PersonalAccessTokenResult;

readonly class AuthTokenDTO
{
    public function __construct(
        public User $user,
        public PersonalAccessTokenResult $token,
    ) {
    }
}
