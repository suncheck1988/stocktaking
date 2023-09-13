<?php

declare(strict_types=1);

namespace App\Store\Model\Position;

use App\Application\ValueObject\EnumValueObject;

/**
 * @method static self active()
 * @method static self inactive()
 */
final class Status extends EnumValueObject
{
    public const ACTIVE = 100;
    public const INACTIVE = 200;

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->value === self::INACTIVE;
    }
}
