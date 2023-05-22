<?php

declare(strict_types=1);

namespace App\Client\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\ValueObject\Uuid;
use App\Client\Dto\ClientSearchDto;
use App\Client\Model\Client\Client;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class ClientRepository extends AbstractRepository
{
    public function add(Client $client): void
    {
        $this->entityManager->persist($client);
    }

    public function remove(Client $client): void
    {
        $this->entityManager->remove($client);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(?ClientSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('cl');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(cl) as clCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Client[]
     */
    public function fetchAll(
        ?ClientSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('cl');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('cl.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var Client[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function get(Uuid $id): Client
    {
        /** @var Client|null $model */
        $model = $this->entityRepository->find($id);
        if ($model === null) {
            throw new NotFoundException(sprintf('Client with id %s not found', $id->getValue()));
        }

        return $model;
    }

    public function getByUserId(Uuid $id): Client
    {
        /** @var Client|null $model */
        $model = $this->entityRepository->findBy(['user' => $id]);
        if ($model === null) {
            throw new NotFoundException(sprintf('Client with user id %s not found', $id->getValue()));
        }

        return $model;
    }

    private function applySearchDto(QueryBuilder $qb, ClientSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('cl.id = :id')->setParameter('id', $searchDto->id);
        }
    }

    protected function getModelClassName(): string
    {
        return Client::class;
    }
}
