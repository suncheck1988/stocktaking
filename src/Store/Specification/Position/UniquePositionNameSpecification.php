<?php

declare(strict_types=1);

namespace App\Store\Specification\Position;

use App\Store\Model\Position\Position;
use App\Store\Repository\PositionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniquePositionNameSpecification
{
    public function __construct(
        private readonly PositionRepository $positionRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(Position $position): bool
    {
        return !$this->positionRepository->existByName($position);
    }
}
