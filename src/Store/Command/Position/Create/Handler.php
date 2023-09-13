<?php

declare(strict_types=1);

namespace App\Store\Command\Position\Create;

use App\Application\ValueObject\Amount;
use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Model\Position\Position;
use App\Store\Repository\CategoryRepository;
use App\Store\Repository\PositionRepository;
use App\Store\Repository\UnitRepository;
use App\Store\Repository\VatRepository;
use App\Store\Repository\WarehouseRepository;
use App\Store\Specification\Position\UniquePositionBalanceWarehouseSpecification;
use App\Store\Specification\Position\UniquePositionNameSpecification;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly PositionRepository $positionRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly WarehouseRepository $warehouseRepository,
        private readonly VatRepository $vatRepository,
        private readonly UnitRepository $unitRepository,
        private readonly UniquePositionNameSpecification $uniquePositionNameSpecification,
        private readonly UniquePositionBalanceWarehouseSpecification $uniquePositionBalanceWarehouseSpecification,
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

        $category = $this->categoryRepository->get(new Uuid($command->getCategoryId()), $client);

        $vat = null;
        $vatId = $command->getVatId();
        if ($vatId !== null) {
            $vat = $this->vatRepository->get(new Uuid($vatId), $client);
        }

        $unit = $this->unitRepository->get(new Uuid($command->getUnitId()), $client);

        $position = new Position(
            Uuid::generate(),
            $client,
            $category,
            $command->getName(),
            $command->getDescription(),
            Amount::fromCurrency($command->getPrice()),
            $vat,
            $unit,
            new DateTimeImmutable(),
            $this->uniquePositionNameSpecification
        );

        $this->positionRepository->add($position);

        foreach ($command->getBalance() as $item) {
            $warehouse = $this->warehouseRepository->get(new Uuid($item->getWarehouseId()), $client);
            $position->updateBalance(
                $warehouse,
                $item->getQuantity(),
                $this->uniquePositionBalanceWarehouseSpecification
            );
        }

        $this->flusher->flush();
    }
}
