<?php

/** @noinspection DoctrineTypeDeprecatedInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\User\Dto\User;

use App\Application\Dto\JsonResponseInterface;
use App\Auth\Model\User\User;
use App\Auth\Model\User\UserPermission;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'UserResponse',
        title: 'User',
        description: 'User response',
        required: ['id', 'name', 'email', 'permissions', 'role', 'status']
    )
]
class UserResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property]
        private readonly string $name,
        #[OA\Property]
        private readonly string $email,
        #[OA\Property(
            property: 'permissions',
            type: 'array',
            items: new OA\Items(
                type: 'int'
            )
        )]
        private readonly array $permissions,
        #[OA\Property]
        private readonly int $role,
        #[OA\Property]
        private readonly int $status
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'permissions' => $this->permissions,
            'role' => $this->role,
            'status' => $this->status,
        ];
    }

    /**
     * @param User $model
     * @throws Exception
     */
    public static function fromModel($model): self
    {
        $permissions = array_map(
            static fn (UserPermission $userPermission) => $userPermission->getPermission()->getValue(),
            $model->getUserPermissions()
        );

        return new self(
            $model->getId()->getValue(),
            $model->getName(),
            $model->getEmail()->getValue(),
            $permissions,
            $model->getRole()->getValue(),
            $model->getStatus()->getValue()
        );
    }
}
