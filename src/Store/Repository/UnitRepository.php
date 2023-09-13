<?php

declare(strict_types=1);

namespace App\Store\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Dto\UnitSearchDto;
use App\Store\Model\Unit\Unit;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class UnitRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(Unit $unit): void
    {
        $this->entityManager->persist($unit);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(Client $client, ?UnitSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('un');

        $qb->where('un.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(un) as unCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(Uuid $id, Client $client): Unit
    {
        /** @var Unit|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('un')
            ->where('un.id = :id')
            ->andWhere('un.client = :client')
            ->setParameter('id', $id->getValue())
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();

        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Единица измерения с id %s, клиента %s не найдена',
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
    public function existByName(Unit $unit): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('un')
            ->select('COUNT(un)')
            ->where('un.client = :client')
            ->andWhere('LOWER(un.name) = LOWER(:name)')
            ->andWhere('un.id <> :id')
            ->setParameter('client', $unit->getClient())
            ->setParameter('name', $unit->getName())
            ->setParameter('id', $unit->getId()->getValue());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return Unit[]
     */
    public function fetchAll(
        Client $client,
        ?UnitSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('un');

        $qb->where('un.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('un.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var Unit[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, UnitSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('un.id = :id')->setParameter('id', $searchDto->id);
        }

        if ($searchDto->name !== null && $searchDto->name !== '') {
            $qb->andWhere('LOWER(un.name) LIKE LOWER(:name)')->setParameter('name', '%' . $searchDto->name . '%');
        }

        if ($searchDto->status !== null) {
            $qb->andWhere('un.status = :status')->setParameter('status', $searchDto->status);
        }
    }

    protected function getModelClassName(): string
    {
        return Unit::class;
    }
}
