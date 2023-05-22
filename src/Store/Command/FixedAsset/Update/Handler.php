<?php

declare(strict_types=1);

namespace App\Store\Command\FixedAsset\Update;

use App\Application\ValueObject\Amount;
use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Client\Repository\CounterpartyRepository;
use App\Client\Repository\EmployeeRepository;
use App\Data\Flusher;
use App\Store\Repository\CategoryRepository;
use App\Store\Repository\FixedAssetRepository;
use App\Store\Repository\UnitRepository;
use App\Store\Repository\VatRepository;
use App\Store\Repository\WarehouseRepository;
use App\Store\Specification\FixedAsset\FixedAssetSpecification;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly FixedAssetRepository $fixedAssetRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly WarehouseRepository $warehouseRepository,
        private readonly VatRepository $vatRepository,
        private readonly UnitRepository $unitRepository,
        private readonly CounterpartyRepository $counterpartyRepository,
        private readonly EmployeeRepository $employeeRepository,
        private readonly FixedAssetSpecification $fixedAssetSpecification,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function handle(Command $command): void
    {
        $client = $this->authContext->getClient();

        $fixedAsset = $this->fixedAssetRepository->get(new Uuid($command->getId()), $client);

        $category = $this->categoryRepository->get(new Uuid($command->getCategoryId()), $client);

        $employee = null;
        $employeeId = $command->getFinanciallyResponsiblePersonId();
        if ($employeeId !== null) {
            $employee = $this->employeeRepository->get(new Uuid($employeeId), $client);
        }

        $counterparty = null;
        $counterpartyId = $command->getCounterpartyId();
        if ($counterpartyId !== null) {
            $counterparty = $this->counterpartyRepository->get(new Uuid($counterpartyId), $client);
        }

        $warehouse = null;
        $warehouseId = $command->getWarehouseId();
        if ($warehouseId !== null) {
            $warehouse = $this->warehouseRepository->get(new Uuid($warehouseId), $client);
        }

        $unit = $this->unitRepository->get(new Uuid($command->getUnitId()), $client);

        $vat = null;
        $vatId = $command->getVatId();
        if ($vatId !== null) {
            $vat = $this->vatRepository->get(new Uuid($vatId), $client);
        }

        $fixedAsset->update(
            $category,
            $employee,
            $counterparty,
            $warehouse,
            $command->getName(),
            $command->getDescription(),
            $command->getSerialNumber(),
            $command->getInventoryNumber(),
            $unit,
            Amount::fromCurrency($command->getPurchasePrice()),
            $vat,
            $this->fixedAssetSpecification
        );

        $this->flusher->flush();
    }
}
