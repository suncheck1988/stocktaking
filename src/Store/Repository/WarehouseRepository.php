<?php

declare(strict_types=1);

namespace App\Store\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Dto\WarehouseSearchDto;
use App\Store\Model\Position\PositionBalance\PositionBalance;
use App\Store\Model\Warehouse\Warehouse;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

final class WarehouseRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(Warehouse $warehouse): void
    {
        $this->entityManager->persist($warehouse);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(Client $client, ?WarehouseSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('w');

        $qb->where('w.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(w) as wCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(Uuid $id, Client $client): Warehouse
    {
        /** @var Warehouse|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('w')
            ->where('w.id = :id')
            ->andWhere('w.client = :client')
            ->setParameter('id', $id->getValue())
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();

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
        Client $client,
        ?WarehouseSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('w');

        $qb->where('w.client = :client')
            ->setParameter('client', $client);

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

        if ($searchDto->name !== null && $searchDto->name !== '') {
            $qb->andWhere('LOWER(w.name) LIKE LOWER(:name)')->setParameter('name', '%' . $searchDto->name . '%');
        }

        if ($searchDto->status !== null) {
            $qb->andWhere('w.status = :status')->setParameter('status', $searchDto->status);
        }

        if ($searchDto->withoutBalanceByPositionId !== null) {
            $sub = $this->entityRepository->createQueryBuilder('w01');
            $sub
                ->innerJoin(
                    PositionBalance::class,
                    'pb',
                    Join::WITH,
                    'pb.warehouse = w'
                )
                ->andWhere('pb.position = :position');

            $qb->andWhere($qb->expr()->notIn('w.id', $sub->getDQL()))
                ->setParameter('position', $searchDto->withoutBalanceByPositionId);
        }
    }

    protected function getModelClassName(): string
    {
        return Warehouse::class;
    }
}
