<?php

declare(strict_types=1);

namespace App\Auth\Service\User;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\User;
use Assert\AssertionFailedException;

class UserPermissionUpdater
{
    /**
     * @param int[] $permissions
     *
     * @throws AssertionFailedException
     */
    public function update(User $user, array $permissions): void
    {
        $actualIds = [];

        foreach ($permissions as $value) {
            $permission = new Permission($value);
            $exists = false;
            foreach ($user->getUserPermissions() as $userPermission) {
                if ($userPermission->getPermission()->isEqualTo($permission)) {
                    $exists = true;
                    $actualIds[] = (string)$userPermission->getId();
                    break;
                }
            }

            if (!$exists) {
                $userPermission = $user->addUserPermission(Uuid::generate(), $permission);
                $actualIds[] = (string)$userPermission->getId();
            }
        }

        foreach ($user->getUserPermissions() as $userPermission) {
            if (!\in_array((string)$userPermission->getId(), $actualIds, true)) {
                $user->removeUserPermission($userPermission);
            }
        }
    }
}
