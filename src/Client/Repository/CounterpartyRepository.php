<?php

declare(strict_types=1);

namespace App\Client\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Dto\CounterpartySearchDto;
use App\Client\Model\Client\Client;
use App\Client\Model\Counterparty\Counterparty;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class CounterpartyRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(Counterparty $counterparty): void
    {
        $this->entityManager->persist($counterparty);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(Client $client, ?CounterpartySearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('cou');

        $qb->where('cou.client = :client')
            ->setParameter('client', $client);

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
        Client $client,
        ?CounterpartySearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('cou');

        $qb->where('cou.client = :client')
            ->setParameter('client', $client);

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

    /**
     * @throws NonUniqueResultException
     */
    public function get(Uuid $id, Client $client): Counterparty
    {
        /** @var Counterparty|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('cou')
            ->where('cou.id = :id')
            ->andWhere('cou.client = :client')
            ->setParameter('id', $id->getValue())
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();

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

    private function applySearchDto(QueryBuilder $qb, CounterpartySearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('cou.id = :id')->setParameter('id', $searchDto->id);
        }

        if ($searchDto->name !== null && $searchDto->name !== '') {
            $qb->andWhere('LOWER(cou.name) LIKE LOWER(:name)')->setParameter('name', '%' . $searchDto->name . '%');
        }

        if ($searchDto->email !== null && $searchDto->email !== '') {
            $qb->andWhere('LOWER(cou.email) LIKE LOWER(:email)')->setParameter('email', '%' . $searchDto->email . '%');
        }

        if ($searchDto->status !== null) {
            $qb->andWhere('cou.status = :status')->setParameter('status', $searchDto->status);
        }
    }

    protected function getModelClassName(): string
    {
        return Counterparty::class;
    }
}
