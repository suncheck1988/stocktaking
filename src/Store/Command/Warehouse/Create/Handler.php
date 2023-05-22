<?php

declare(strict_types=1);

namespace App\Store\Command\Warehouse\Create;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Model\Warehouse\Warehouse;
use App\Store\Repository\WarehouseRepository;
use App\Store\Specification\Warehouse\UniqueWarehouseNameSpecification;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly WarehouseRepository $warehouseRepository,
        private readonly UniqueWarehouseNameSpecification $uniqueWarehouseNameSpecification,
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

        $warehouse = new Warehouse(
            Uuid::generate(),
            $client,
            $command->getName(),
            new DateTimeImmutable(),
            $this->uniqueWarehouseNameSpecification
        );

        $this->warehouseRepository->add($warehouse);

        $this->flusher->flush();
    }
}
