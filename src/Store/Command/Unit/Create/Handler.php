<?php

declare(strict_types=1);

namespace App\Store\Command\Unit\Create;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Model\Unit\Unit;
use App\Store\Repository\UnitRepository;
use App\Store\Specification\Unit\UniqueUnitNameSpecification;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly UnitRepository $unitRepository,
        private readonly UniqueUnitNameSpecification $uniqueUnitNameSpecification,
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

        $unit = new Unit(
            Uuid::generate(),
            $client,
            $command->getName(),
            new DateTimeImmutable(),
            $this->uniqueUnitNameSpecification
        );

        $this->unitRepository->add($unit);

        $this->flusher->flush();
    }
}
