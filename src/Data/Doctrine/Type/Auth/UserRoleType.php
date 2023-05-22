<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Auth;

use App\Auth\Model\User\Role;
use App\Data\Doctrine\Type\EnumType;

final class UserRoleType extends EnumType
{
    public const NAME = 'auth_user_role';

    protected function getClassName(): string
    {
        return Role::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
