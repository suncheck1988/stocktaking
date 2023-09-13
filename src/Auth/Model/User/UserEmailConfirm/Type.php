<?php

declare(strict_types=1);

namespace App\Auth\Model\User\UserEmailConfirm;

use App\Application\ValueObject\EnumValueObject;

/**
 * @method static self registration()
 * @method static self password_reset()
 */
class Type extends EnumValueObject
{
    public const REGISTRATION = 100;
    public const PASSWORD_RESET = 200;
}
