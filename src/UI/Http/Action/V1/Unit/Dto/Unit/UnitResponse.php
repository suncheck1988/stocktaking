<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Unit\Dto\Unit;

use App\Application\Dto\JsonResponseInterface;
use App\Store\Model\Unit\Unit;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'UnitResponse',
        title: 'Unit',
        description: 'Unit response',
        required: ['id', 'name', 'status']
    )
]
class UnitResponse implements JsonResponseInterface
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
     * @param Unit $model
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
