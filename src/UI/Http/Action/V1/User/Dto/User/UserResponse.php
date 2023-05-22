<?php

/** @noinspection DoctrineTypeDeprecatedInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\User\Dto\User;

use App\Application\Dto\AbstractJsonResponse;
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
        required: ['id', 'role', 'name', 'status']
    )
]
class UserResponse extends AbstractJsonResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property]
        private readonly int $role,
        #[OA\Property]
        private readonly string $name,
        #[OA\Property]
        private readonly int $status,
        #[OA\Property(
            property: 'permissions',
            type: 'array',
            items: new OA\Items(
                type: 'int'
            )
        )]
        private readonly array $permissions
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'name' => $this->name,
            'status' => $this->status,
            'permissions' => $this->permissions,
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
            (string)$model->getId(),
            $model->getRole()->getValue(),
            $model->getName(),
            $model->getStatus()->getValue(),
            $permissions
        );
    }
}
