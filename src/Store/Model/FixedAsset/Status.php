<?php

declare(strict_types=1);

namespace App\Store\Model\FixedAsset;

use App\Application\ValueObject\EnumValueObject;

/**
 * @method static self in_use()
 * @method static self storage()
 * @method static self decommissioned()
 */
final class Status extends EnumValueObject
{
    public const IN_USE = 100;
    public const STORAGE = 200;
    public const DECOMMISSIONED = 300;

    public function isInUse(): bool
    {
        return $this->value === self::IN_USE;
    }

    public function isStorage(): bool
    {
        return $this->value === self::STORAGE;
    }

    public function isDecommissioned(): bool
    {
        return $this->value === self::DECOMMISSIONED;
    }
}
