<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;

class RegisterUserAction
{
    /**
     * @param array{name:string,email:string,password:string,role?:string,country_code:string,currency_code:string} $data
     */
    public function execute(array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? UserRole::Employee->value,
            'country_code' => strtoupper($data['country_code']),
            'currency_code' => strtoupper($data['currency_code']),
        ]);
    }
}
