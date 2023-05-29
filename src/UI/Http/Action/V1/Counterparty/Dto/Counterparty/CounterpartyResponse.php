<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Counterparty\Dto\Counterparty;

use App\Application\Dto\JsonResponseInterface;
use App\Client\Model\Counterparty\Counterparty;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'CounterpartyResponse',
        title: 'Counterparty',
        description: 'Counterparty response',
        required: ['id', 'name', 'status']
    )
]
class CounterpartyResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property]
        private readonly string $name,
        #[OA\Property]
        private readonly ?string $email,
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
            'status' => $this->status,
        ];
    }

    /**
     * @param Counterparty $model
     * @throws Exception
     */
    public static function fromModel($model): self
    {
        return new self(
            $model->getId()->getValue(),
            $model->getName(),
            $model->getEmail()?->getValue(),
            $model->getStatus()->getValue()
        );
    }
}
