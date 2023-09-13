<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Vat\Dto\Vat;

use App\Application\Dto\JsonResponseInterface;
use App\Store\Model\Vat\Vat;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'VatResponse',
        title: 'Vat',
        description: 'Vat response',
        required: ['id', 'value', 'isDefault', 'status']
    )
]
class VatResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property]
        private readonly int $value,
        #[OA\Property]
        private readonly bool $isDefault,
        #[OA\Property]
        private readonly int $status
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'isDefault' => $this->isDefault,
            'status' => $this->status,
        ];
    }

    /**
     * @param Vat $model
     * @throws Exception
     */
    public static function fromModel($model): self
    {
        return new self(
            $model->getId()->getValue(),
            $model->getValue(),
            $model->isDefault(),
            $model->getStatus()->getValue()
        );
    }
}
