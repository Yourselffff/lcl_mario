<?php

namespace App\Auth;

use Illuminate\Auth\GenericUser;

class ToadUser extends GenericUser
{
    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value): void
    {
        // no-op
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}