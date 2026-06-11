<?php

namespace App\Actions;

use App\DTOs\AuthTokenDTO;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class LoginUserAction
{
    /**
     * @param array{email:string,password:string} $credentials
     */
    public function execute(array $credentials): AuthTokenDTO
    {
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return new AuthTokenDTO(
            user: $user,
            token: $user->createToken('api-token'),
        );
    }
}
