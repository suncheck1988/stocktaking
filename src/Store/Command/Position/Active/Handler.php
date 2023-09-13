<?php

declare(strict_types=1);

namespace App\Store\Command\Position\Active;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Repository\PositionRepository;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly PositionRepository $positionRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     */
    public function handle(Command $command): void
    {
        $position = $this->positionRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $position->active();

        $this->flusher->flush();
    }
}
