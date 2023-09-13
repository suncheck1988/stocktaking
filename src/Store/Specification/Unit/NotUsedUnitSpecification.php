<?php

declare(strict_types=1);

namespace App\Store\Specification\Unit;

use App\Store\Model\Unit\Unit;
use App\Store\Repository\PositionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class NotUsedUnitSpecification
{
    public function __construct(
        private readonly PositionRepository $positionRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(Unit $unit): bool
    {
        return !$this->positionRepository->existWithUnit($unit);
    }
}
