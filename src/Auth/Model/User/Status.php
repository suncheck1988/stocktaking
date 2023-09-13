<?php

declare(strict_types=1);

namespace App\Auth\Model\User;

use App\Application\ValueObject\EnumValueObject;

/**
 * @method static self new()
 * @method static self active()
 * @method static self blocked()
 */
final class Status extends EnumValueObject
{
    public const NEW = 100;
    public const ACTIVE = 200;
    public const BLOCKED = 300;

    public function isNew(): bool
    {
        return $this->value === self::NEW;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isBlocked(): bool
    {
        return $this->value === self::BLOCKED;
    }
}
