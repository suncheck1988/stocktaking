<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Employee\Dto\Employee;

use App\Application\Dto\JsonResponseInterface;
use App\Client\Model\Employee\Employee;
use App\UI\Http\Action\V1\User\Dto\User\UserResponse;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'EmployeeResponse',
        title: 'Employee',
        description: 'Employee response',
        required: ['id', 'user', 'isFinanciallyResponsiblePerson']
    )
]
class EmployeeResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property(
            property: 'user',
            ref: '#/components/schemas/UserResponse',
            type: 'json'
        )]
        private readonly JsonResponseInterface $user,
        #[OA\Property]
        private readonly bool $isFinanciallyResponsiblePerson,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user,
            'isFinanciallyResponsiblePerson' => $this->isFinanciallyResponsiblePerson,
        ];
    }

    /**
     * @param Employee $model
     * @throws Exception
     */
    public static function fromModel($model): self
    {
        return new self(
            $model->getId()->getValue(),
            UserResponse::fromModel($model->getUser()),
            $model->isFinanciallyResponsiblePerson()
        );
    }
}
