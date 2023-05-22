<?php

declare(strict_types=1);

namespace App\Order\Model\Order\OrderItem;

use App\Application\ValueObject\EnumValueObject;

/**
 * @method static self new()
 * @method static self confirmed()
 * @method static self changed()
 * @method static self added()
 * @method static self deleted()
 */
final class Status extends EnumValueObject
{
    public const NEW = 100;
    public const CONFIRMED = 200;
    public const CHANGED = 300;
    public const ADDED = 400;
    public const DELETED = 500;

    public function isNew(): bool
    {
        return $this->value === self::NEW;
    }

    public function isConfirmed(): bool
    {
        return $this->value === self::CONFIRMED;
    }

    public function isChanged(): bool
    {
        return $this->value === self::CHANGED;
    }

    public function isAdded(): bool
    {
        return $this->value === self::ADDED;
    }

    public function isDeleted(): bool
    {
        return $this->value === self::DELETED;
    }
}
