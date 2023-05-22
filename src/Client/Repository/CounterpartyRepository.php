<?php

declare(strict_types=1);

namespace App\Client\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\ValueObject\Uuid;
use App\Client\Dto\CounterpartySearchDto;
use App\Client\Model\Client\Client;
use App\Client\Model\Counterparty\Counterparty;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class CounterpartyRepository extends AbstractRepository
{
    public function add(Counterparty $counterparty): void
    {
        $this->entityManager->persist($counterparty);
    }

    public function remove(Counterparty $counterparty): void
    {
        $this->entityManager->remove($counterparty);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(?CounterpartySearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('cou');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(cou) as couCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Counterparty[]
     */
    public function fetchAll(
        ?CounterpartySearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('cou');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('cou.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var Counterparty[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function get(Uuid $id, Client $client): Counterparty
    {
        /** @var Counterparty|null $model */
        $model = $this->entityRepository->findBy(['id' => $id, 'client' => $client]);
        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Контрагент с id %s, клиента %s не найден',
                    $id->getValue(),
                    $client->getId()->getValue()
                )
            );
        }

        return $model;
    }

    public function getByUserId(Uuid $id): Counterparty
    {
        /** @var Counterparty|null $model */
        $model = $this->entityRepository->findBy(['user' => $id]);
        if ($model === null) {
            throw new NotFoundException(sprintf('Контрагент с id %s не найден', $id->getValue()));
        }

        return $model;
    }

    private function applySearchDto(QueryBuilder $qb, CounterpartySearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('сщг.id = :id')->setParameter('id', $searchDto->id);
        }
    }

    protected function getModelClassName(): string
    {
        return Counterparty::class;
    }
}
