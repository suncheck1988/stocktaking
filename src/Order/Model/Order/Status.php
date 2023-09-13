<?php

declare(strict_types=1);

namespace App\Order\Model\Order;

use App\Application\ValueObject\EnumValueObject;

/**
 * @method static self new()
 * @method static self confirmed()
 * @method static self processed()
 * @method static self sent()
 * @method static self done()
 * @method static self canceled()
 */
final class Status extends EnumValueObject
{
    public const NEW = 100;
    public const CONFIRMED = 200;
    public const PROCESSED = 300;
    public const SENT = 400;
    public const DONE = 500;
    public const CANCELED = 600;

    public function isNew(): bool
    {
        return $this->value === self::NEW;
    }

    public function isConfirmed(): bool
    {
        return $this->value === self::CONFIRMED;
    }

    public function isProcessed(): bool
    {
        return $this->value === self::PROCESSED;
    }

    public function isSent(): bool
    {
        return $this->value === self::SENT;
    }

    public function isDone(): bool
    {
        return $this->value === self::DONE;
    }

    public function isCanceled(): bool
    {
        return $this->value === self::CANCELED;
    }
}
