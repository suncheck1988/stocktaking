<?php

declare(strict_types=1);

namespace App\Auth\Model\User;

use App\Application\ValueObject\EnumValueObject;

/**
 * @method static self admin()
 * @method static self client()
 * @method static self client_employee()
 */
final class Role extends EnumValueObject
{
    public const ADMIN = 100;
    public const CLIENT = 200;
    public const CLIENT_EMPLOYEE = 300;

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    public function isClient(): bool
    {
        return $this->value === self::CLIENT;
    }

    public function isClientEmployee(): bool
    {
        return $this->value === self::CLIENT_EMPLOYEE;
    }
}
