<?php

declare(strict_types=1);

namespace App\Store\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Dto\WarehouseSearchDto;
use App\Store\Model\Warehouse\Warehouse;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class WarehouseRepository extends AbstractRepository
{
    public function add(Warehouse $warehouse): void
    {
        $this->entityManager->persist($warehouse);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(?WarehouseSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('w');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(w) as wCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function get(Uuid $id, Client $client): Warehouse
    {
        /** @var Warehouse|null $model */
        $model = $this->entityRepository->findBy(['id' => $id, 'client' => $client]);
        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Склад с id %s, клиента %s не найдена',
                    $id->getValue(),
                    $client->getId()->getValue()
                )
            );
        }

        return $model;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function existByName(Warehouse $warehouse): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('w')
            ->select('COUNT(w)')
            ->where('w.client = :client')
            ->andWhere('LOWER(w.name) = LOWER(:name)')
            ->setParameter('client', $warehouse->getClient())
            ->setParameter('name', $warehouse->getName());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return Warehouse[]
     */
    public function fetchAll(
        ?WarehouseSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('w');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('w.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var Warehouse[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, WarehouseSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('w.id = :id')->setParameter('id', $searchDto->id);
        }
    }

    protected function getModelClassName(): string
    {
        return Warehouse::class;
    }
}
