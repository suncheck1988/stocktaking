<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Position\Dto\Position;

use App\Application\Dto\JsonResponseInterface;
use App\Store\Model\Position\Position;
use App\Store\Model\Position\PositionBalance\PositionBalance;
use App\UI\Http\Action\V1\Category\Dto\Category\CategoryResponse;
use App\UI\Http\Action\V1\Unit\Dto\Unit\UnitResponse;
use App\UI\Http\Action\V1\Vat\Dto\Vat\VatResponse;
use App\UI\Http\Action\V1\Warehouse\Dto\Warehouse\WarehouseResponse;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'PositionResponse',
        title: 'Position',
        description: 'Position response',
        required: ['id', 'name', 'category', 'price', 'unit', 'balance', 'status']
    )
]
class PositionResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property]
        private readonly string $name,
        #[OA\Property]
        private readonly ?string $description,
        #[OA\Property]
        private readonly float $price,
        #[OA\Property(
            property: 'vat',
            ref: VatResponse::class,
            type: 'json'
        )]
        private readonly ?JsonResponseInterface $vat,
        #[OA\Property(
            property: 'unit',
            ref: UnitResponse::class,
            type: 'json'
        )]
        private readonly JsonResponseInterface $unit,
        #[OA\Property(
            property: 'balances',
            type: 'array',
            items: new OA\Items(
                ref: PositionBalance::class,
                type: 'object'
            )
        )]
        private readonly array $balance,
        #[OA\Property(
            property: 'category',
            ref: CategoryResponse::class,
            type: 'json'
        )]
        private readonly JsonResponseInterface $category,
        #[OA\Property]
        private readonly int $status
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'vat' => $this->vat,
            'unit' => $this->unit,
            'balance' => $this->balance,
            'category' => $this->category,
            'status' => $this->status,
        ];
    }

    /**
     * @param Position $model
     * @throws Exception
     */
    public static function fromModel($model): self
    {
        $balance = [];
        foreach ($model->getPositionBalances() as $item) {
            $balance[] = [
                'id' => $item->getId()->getValue(),
                'warehouse' => WarehouseResponse::fromModel($item->getWarehouse()),
                'quantity' => $item->getQuantity(),
                'status' => $item->getStatus()->getValue(),
            ];
        }

        $vat = $model->getVat();
        if ($vat !== null) {
            $vat = VatResponse::fromModel($vat);
        }

        return new self(
            $model->getId()->getValue(),
            $model->getName(),
            $model->getDescription(),
            $model->getPrice()->toCurrency(),
            $vat,
            UnitResponse::fromModel($model->getUnit()),
            $balance,
            CategoryResponse::fromModel($model->getCategory()),
            $model->getStatus()->getValue()
        );
    }
}
