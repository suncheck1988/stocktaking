<?php

declare(strict_types=1);

namespace App\Store\Command\FixedAsset\InUse;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Repository\FixedAssetRepository;
use Assert\AssertionFailedException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly FixedAssetRepository $fixedAssetRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     */
    public function handle(Command $command): void
    {
        $fixedAsset = $this->fixedAssetRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $fixedAsset->inUse();

        $this->flusher->flush();
    }
}
