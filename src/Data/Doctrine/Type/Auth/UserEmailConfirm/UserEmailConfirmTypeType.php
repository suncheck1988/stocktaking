<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Auth\UserEmailConfirm;

use App\Auth\Model\User\UserEmailConfirm\Type;
use App\Data\Doctrine\Type\EnumType;

class UserEmailConfirmTypeType extends EnumType
{
    public const NAME = 'auth_user_email_confirm_type';

    protected function getClassName(): string
    {
        return Type::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
