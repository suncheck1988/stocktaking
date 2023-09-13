<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\DeliveryType\Dto\DeliveryType;

use App\Application\Dto\JsonResponseInterface;
use App\Order\Model\DeliveryType\DeliveryType;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'DeliveryTypeResponse',
        title: 'DeliveryType',
        description: 'Delivery type',
        required: ['id', 'name', 'status']
    )
]
class DeliveryTypeResponse implements JsonResponseInterface
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
     * @param DeliveryType $model
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
