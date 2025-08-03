<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';


    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::USER => 'Usuário',
        };
    }

    public function canAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function canUser(): bool
    {
        return $this === self::USER;
    }
}
