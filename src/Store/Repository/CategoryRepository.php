<?php

declare(strict_types=1);

namespace App\Store\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Dto\CategorySearchDto;
use App\Store\Model\Category\Category;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class CategoryRepository extends AbstractRepository
{
    public function add(Category $category): void
    {
        $this->entityManager->persist($category);
    }

    public function remove(Category $category): void
    {
        $this->entityManager->remove($category);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(?CategorySearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('c');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(c) as cCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function get(Uuid $id, Client $client): Category
    {
        /** @var Category|null $model */
        $model = $this->entityRepository->findBy(['id' => $id, 'client' => $client]);
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
        ?CategorySearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('c');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('c.createdAt', 'DESC');

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
    }

    protected function getModelClassName(): string
    {
        return Category::class;
    }
}
