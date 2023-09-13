<?php

declare(strict_types=1);

namespace App\Store\Command\Unit\Inactive;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Repository\UnitRepository;
use App\Store\Specification\Unit\NotUsedUnitSpecification;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly UnitRepository $unitRepository,
        private readonly NotUsedUnitSpecification $notUsedUnitSpecification,
        private readonly Flusher $flusher
    ) {
    }


    /**
     * @throws NonUniqueResultException
     * @throws AssertionFailedException
     * @throws NoResultException
     */
    public function handle(Command $command): void
    {
        $unit = $this->unitRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $unit->inactive($this->notUsedUnitSpecification);

        $this->flusher->flush();
    }
}
