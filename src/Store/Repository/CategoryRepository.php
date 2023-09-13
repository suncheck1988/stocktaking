<?php

declare(strict_types=1);

namespace App\Store\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Dto\CategorySearchDto;
use App\Store\Model\Category\Category;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

final class CategoryRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function remove(Category $category): void
    {
        $this->entityManager->remove($category);
    }

    public function add(Category $category): void
    {
        $this->entityManager->persist($category);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(Client $client, ?CategorySearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('c');

        $qb->where('c.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(c) as cCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(Uuid $id, Client $client): Category
    {
        /** @var Category|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('c')
            ->where('c.id = :id')
            ->andWhere('c.client = :client')
            ->setParameter('id', $id->getValue())
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();

        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Категория с id %s, клиента %s не найдена',
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
    public function existByName(Category $category): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.client = :client')
            ->andWhere('LOWER(c.name) = LOWER(:name)')
            ->setParameter('client', $category->getClient())
            ->setParameter('name', $category->getName());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return Category[]
     */
    public function fetchAll(
        Client $client,
        ?CategorySearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('c');

        $qb->where('c.client = :client')
            ->setParameter('client', $client);

        $qb->orderBy('c.createdAt', 'DESC');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var Category[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, CategorySearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('c.id = :id')->setParameter('id', $searchDto->id);
        }

        if ($searchDto->name !== null && $searchDto->name !== '') {
            $qb->andWhere('LOWER(c.name) LIKE LOWER(:name)')->setParameter('name', '%' . $searchDto->name . '%');
        }

        if ($searchDto->onlyRoot) {
            $qb->andWhere('c.parent IS NULL');
        }

        if ($searchDto->status !== null) {
            $qb->andWhere('c.status = :status')->setParameter('status', $searchDto->status);
        }
    }

    protected function getModelClassName(): string
    {
        return Category::class;
    }
}
