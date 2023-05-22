<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Auth;

use App\Auth\Model\User\Email;
use App\Data\Doctrine\Type\StringType;

class UserEmailType extends StringType
{
    public const NAME = 'auth_user_email';

    protected function getClassName(): string
    {
        return Email::class;
    }
}
