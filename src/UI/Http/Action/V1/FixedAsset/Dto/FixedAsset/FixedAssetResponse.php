<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\FixedAsset\Dto\FixedAsset;

use App\Application\Dto\JsonResponseInterface;
use App\Store\Model\FixedAsset\FixedAsset;
use App\UI\Http\Action\V1\Category\Dto\Category\CategoryResponse;
use App\UI\Http\Action\V1\Counterparty\Dto\Counterparty\CounterpartyResponse;
use App\UI\Http\Action\V1\Unit\Dto\Unit\UnitResponse;
use App\UI\Http\Action\V1\User\Dto\User\UserShortResponse;
use App\UI\Http\Action\V1\Vat\Dto\Vat\VatResponse;
use App\UI\Http\Action\V1\Warehouse\Dto\Warehouse\WarehouseResponse;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'FixedAssetResponse',
        title: 'FixedAsset',
        description: 'Fixed asset response',
        required: [
            'id',
            'financiallyResponsiblePerson',
            'category',
            'name',
            'serialNumber',
            'inventoryNumber',
            'unit',
            'purchasePrice',
            'residualValue',
            'status',
        ]
    )
]
class FixedAssetResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property(
            property: 'financiallyResponsiblePerson',
            ref: UserShortResponse::class,
            type: 'json'
        )]
        private readonly JsonResponseInterface $financiallyResponsiblePerson,
        #[OA\Property(
            property: 'category',
            ref: CategoryResponse::class,
            type: 'json'
        )]
        private readonly JsonResponseInterface $category,
        #[OA\Property(
            property: 'counterparty',
            ref: CounterpartyResponse::class,
            type: 'json'
        )]
        private readonly ?JsonResponseInterface $counterparty,
        #[OA\Property(
            property: 'warehouse',
            ref: WarehouseResponse::class,
            type: 'json'
        )]
        private readonly ?JsonResponseInterface $warehouse,
        #[OA\Property]
        private readonly string $name,
        #[OA\Property]
        private readonly ?string $description,
        #[OA\Property]
        private readonly string $serialNumber,
        #[OA\Property]
        private readonly string $inventoryNumber,
        #[OA\Property(
            property: 'unit',
            ref: UnitResponse::class,
            type: 'json'
        )]
        private readonly JsonResponseInterface $unit,
        #[OA\Property]
        private readonly float $purchasePrice,
        #[OA\Property(
            property: 'vat',
            ref: VatResponse::class,
            type: 'json'
        )]
        private readonly ?JsonResponseInterface $vat,
        #[OA\Property]
        private readonly float $residualValue,
        #[OA\Property]
        private readonly ?string $inUseAt,
        #[OA\Property]
        private readonly ?string $decommissionedAt,
        #[OA\Property]
        private readonly int $status
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'financiallyResponsiblePerson' => $this->financiallyResponsiblePerson,
            'category' => $this->category,
            'counterparty' => $this->counterparty,
            'warehouse' => $this->warehouse,
            'name' => $this->name,
            'description' => $this->description,
            'serialNumber' => $this->serialNumber,
            'inventoryNumber' => $this->inventoryNumber,
            'unit' => $this->unit,
            'purchasePrice' => $this->purchasePrice,
            'vat' => $this->vat,
            'residualValue' => $this->residualValue,
            'inUseAt' => $this->inUseAt,
            'decommissionedAt' => $this->decommissionedAt,
            'status' => $this->status,
        ];
    }

    /**
     * @param FixedAsset $model
     * @throws Exception
     */
    public static function fromModel($model): self
    {
        $counterparty = $model->getCounterparty();
        if ($counterparty !== null) {
            $counterparty = CounterpartyResponse::fromModel($counterparty);
        }

        $warehouse = $model->getWarehouse();
        if ($warehouse !== null) {
            $warehouse = WarehouseResponse::fromModel($warehouse);
        }

        $vat = $model->getVat();
        if ($vat !== null) {
            $vat = VatResponse::fromModel($vat);
        }

        return new self(
            $model->getId()->getValue(),
            UserShortResponse::fromModel($model->getFinanciallyResponsiblePerson()),
            CategoryResponse::fromModel($model->getCategory()),
            $counterparty,
            $warehouse,
            $model->getName(),
            $model->getDescription(),
            $model->getSerialNumber(),
            $model->getInventoryNumber(),
            UnitResponse::fromModel($model->getUnit()),
            $model->getPurchasePrice()->toCurrency(),
            $vat,
            $model->getResidualValue()->toCurrency(),
            $model->inUseAt()?->format(DATE_ATOM),
            $model->decommissionedAt()?->format(DATE_ATOM),
            $model->getStatus()->getValue()
        );
    }
}
