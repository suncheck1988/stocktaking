<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\PaymentMethod\Dto\PaymentMethod;

use App\Application\Dto\JsonResponseInterface;
use App\Order\Model\PaymentMethod\PaymentMethod;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'PaymentMethodResponse',
        title: 'PaymentMethod',
        description: 'Payment response',
        required: ['id', 'name', 'status']
    )
]
class PaymentMethodResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property]
        private readonly string $name,
        #[OA\Property]
        private readonly int $status
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
        ];
    }

    /**
     * @param PaymentMethod $model
     * @throws Exception
     */
    public static function fromModel($model): self
    {
        return new self(
            $model->getId()->getValue(),
            $model->getName(),
            $model->getStatus()->getValue()
        );
    }
}
