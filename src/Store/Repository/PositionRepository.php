<?php

declare(strict_types=1);

namespace App\Store\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Dto\PositionSearchDto;
use App\Store\Model\Position\Position;
use App\Store\Model\Unit\Unit;
use App\Store\Model\Warehouse\Warehouse;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class PositionRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(Position $position): void
    {
        $this->entityManager->persist($position);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(Client $client, ?PositionSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('p');

        $qb->where('p.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(p) as pCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(Uuid $id, Client $client): Position
    {
        /** @var Position|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('p')
            ->where('p.id = :id')
            ->andWhere('p.client = :client')
            ->setParameter('id', $id->getValue())
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();

        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Позиция с id %s, клиента %s не найдена',
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
    public function existByName(Position $position): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->where('p.client = :client')
            ->andWhere('LOWER(p.name) = LOWER(:name)')
            ->setParameter('client', $position->getClient())
            ->setParameter('name', $position->getName());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function existWithUnit(Unit $unit): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->where('p.client = :client')
            ->andWhere('p.unit = :unit')
            ->setParameter('client', $unit->getClient())
            ->setParameter('unit', $unit);

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function existBalanceByWarehouse(Position $position, Warehouse $warehouse): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->innerJoin('p.positionBalances', 'pb')
            ->where('p.client = :client')
            ->andWhere('pb.position = :position')
            ->andWhere('pb.warehouse = :warehouse')
            ->setParameter('client', $position->getClient())
            ->setParameter('position', $position)
            ->setParameter('warehouse', $warehouse);

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return Position[]
     */
    public function fetchAll(
        Client $client,
        ?PositionSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('p');

        $qb->where('p.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('p.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var Position[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, PositionSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('p.id = :id')->setParameter('id', $searchDto->id);
        }

        if ($searchDto->name !== null && $searchDto->name !== '') {
            $qb->andWhere('LOWER(p.name) LIKE LOWER(:name)')->setParameter('name', '%' . $searchDto->name . '%');
        }

        if ($searchDto->status !== null) {
            $qb->andWhere('p.status = :status')->setParameter('status', $searchDto->status);
        }
    }

    protected function getModelClassName(): string
    {
        return Position::class;
    }
}
