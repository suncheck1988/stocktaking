<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Auth;

use App\Auth\Model\User\Status;
use App\Data\Doctrine\Type\EnumType;

class UserStatusType extends EnumType
{
    public const NAME = 'auth_user_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
