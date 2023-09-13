<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Auth;

use App\Auth\Model\User\Permission;
use App\Data\Doctrine\Type\IntegerType;

class UserPermissionType extends IntegerType
{
    public const NAME = 'auth_user_permission';

    protected function getClassName(): string
    {
        return Permission::class;
    }
}
