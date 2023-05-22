<?php

declare(strict_types=1);

namespace App\Store\Command\Warehouse\Inactive;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Repository\WarehouseRepository;
use Assert\AssertionFailedException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly WarehouseRepository $warehouseRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     */
    public function handle(Command $command): void
    {
        $warehouse = $this->warehouseRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $warehouse->inactive();

        $this->flusher->flush();
    }
}
