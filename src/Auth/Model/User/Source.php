<?php

declare(strict_types=1);

namespace App\Auth\Model\User;

use App\Application\ValueObject\EnumValueObject;

/**
 * @method static self rkeeper()
 * @method static self console()
 */
final class Source extends EnumValueObject
{
    public const RKEEPER = 100;
    public const CONSOLE = 200;

    public function isRkeeper(): bool
    {
        return $this->value === self::RKEEPER;
    }

    public function isConsole(): bool
    {
        return $this->value === self::CONSOLE;
    }
}
