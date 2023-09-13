<?php

declare(strict_types=1);

namespace App\Store\Command\Position\Update;

use App\Application\ValueObject\Amount;
use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Data\TransactionManager;
use App\Store\Repository\CategoryRepository;
use App\Store\Repository\PositionRepository;
use App\Store\Repository\UnitRepository;
use App\Store\Repository\VatRepository;
use App\Store\Repository\WarehouseRepository;
use App\Store\Specification\Position\UniquePositionBalanceWarehouseSpecification;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Throwable;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly PositionRepository $positionRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly WarehouseRepository $warehouseRepository,
        private readonly VatRepository $vatRepository,
        private readonly UnitRepository $unitRepository,
        private readonly UniquePositionBalanceWarehouseSpecification $uniquePositionBalanceWarehouseSpecification,
        private readonly TransactionManager $transactionManager,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws NoResultException
     * @throws Throwable
     */
    public function handle(Command $command): void
    {
        $client = $this->authContext->getClient();

        $position = $this->positionRepository->get(new Uuid($command->getId()), $client);

        $category = $this->categoryRepository->get(new Uuid($command->getCategoryId()), $client);

        $vat = null;
        $vatId = $command->getVatId();
        if ($vatId !== null) {
            $vat = $this->vatRepository->get(new Uuid($vatId), $client);
        }

        $unit = $this->unitRepository->get(new Uuid($command->getUnitId()), $client);

        $position->update(
            $category,
            $command->getName(),
            $command->getDescription(),
            $vat,
            $unit
        );

        $position->updatePrice(Amount::fromCurrency($command->getPrice()));

        $this->transactionManager->beginTransaction();

        try {
            foreach ($command->getBalance() as $item) {
                $warehouse = $this->warehouseRepository->get(new Uuid($item->getWarehouseId()), $client);

                if ($item->isRemove()) {
                    $position->removeBalance($warehouse);
                } else {
                    $position->updateBalance(
                        $warehouse,
                        $item->getQuantity(),
                        $this->uniquePositionBalanceWarehouseSpecification
                    );
                }
            }

            foreach ($command->getNewBalance() as $item) {
                $warehouse = $this->warehouseRepository->get(new Uuid($item->getWarehouseId()), $client);
                $position->updateBalance(
                    $warehouse,
                    $item->getQuantity(),
                    $this->uniquePositionBalanceWarehouseSpecification
                );
            }

            $this->flusher->flush();
            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
