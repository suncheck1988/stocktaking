<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\User\Dto\User;

use App\Application\Dto\JsonResponseInterface;
use App\Auth\Model\User\User;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'UserShortResponse',
        title: 'UserShort',
        description: 'User short response',
        required: ['id', 'name', 'email', 'role', 'status']
    )
]
class UserShortResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property]
        private readonly string $name,
        #[OA\Property]
        private readonly string $email,
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
        return new self(
            $model->getId()->getValue(),
            $model->getName(),
            $model->getEmail()->getValue(),
            $model->getRole()->getValue(),
            $model->getStatus()->getValue()
        );
    }
}
