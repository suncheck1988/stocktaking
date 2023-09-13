<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Auth\UserEmailConfirm;

use App\Auth\Model\User\UserEmailConfirm\Status;
use App\Data\Doctrine\Type\EnumType;

class UserEmailConfirmStatusType extends EnumType
{
    public const NAME = 'auth_user_email_confirm_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
