<?php

declare(strict_types=1);

namespace App\Auth\Model\User\UserEmailConfirm;

use App\Application\ValueObject\EnumValueObject;

/**
 * @method static self new()
 * @method static self confirmed()
 * @method static self expired()
 */
final class Status extends EnumValueObject
{
    public const NEW = 100;
    public const CONFIRMED = 200;
    public const EXPIRED = 300;

    public function isNew(): bool
    {
        return $this->value === self::NEW;
    }

    public function isConfirmed(): bool
    {
        return $this->value === self::CONFIRMED;
    }

    public function isExpired(): bool
    {
        return $this->value === self::EXPIRED;
    }
}
