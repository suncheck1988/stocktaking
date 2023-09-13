<?php

declare(strict_types=1);

namespace App\Store\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Dto\FixedAssetSearchDto;
use App\Store\Model\FixedAsset\FixedAsset;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class FixedAssetRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(FixedAsset $fixedAsset): void
    {
        $this->entityManager->persist($fixedAsset);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(Client $client, ?FixedAssetSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('fa');

        $qb->where('fa.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(fa) as faCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(Uuid $id, Client $client): FixedAsset
    {
        /** @var FixedAsset|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('fa')
            ->where('fa.id = :id')
            ->andWhere('fa.client = :client')
            ->setParameter('id', $id->getValue())
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();

        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Основное средство с id %s, клиента %s не найдена',
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
    public function existBySerialNumber(FixedAsset $fixedAsset): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('fa')
            ->select('COUNT(fa)')
            ->where('fa.client = :client')
            ->andWhere('LOWER(fa.serialNumber) = LOWER(:serialNumber)')
            ->andWhere('fa.id <> :fixedAsset')
            ->setParameter('client', $fixedAsset->getClient())
            ->setParameter('serialNumber', $fixedAsset->getSerialNumber())
            ->setParameter('fixedAsset', $fixedAsset);

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function existByInventoryNumber(FixedAsset $fixedAsset): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('fa')
            ->select('COUNT(fa)')
            ->where('fa.client = :client')
            ->andWhere('LOWER(fa.inventoryNumber) = LOWER(:inventoryNumber)')
            ->andWhere('fa.id <> :fixedAsset')
            ->setParameter('client', $fixedAsset->getClient())
            ->setParameter('inventoryNumber', $fixedAsset->getInventoryNumber())
            ->setParameter('fixedAsset', $fixedAsset);

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return FixedAsset[]
     */
    public function fetchAll(
        Client $client,
        ?FixedAssetSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('fa');

        $qb->where('fa.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('fa.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var FixedAsset[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, FixedAssetSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('fa.id = :id')->setParameter('id', $searchDto->id);
        }

        if ($searchDto->categoryId !== null && $searchDto->categoryId !== '') {
            $qb->andWhere('fa.category = :categoryId')->setParameter('categoryId', $searchDto->categoryId);
        }

        if ($searchDto->warehouseId !== null && $searchDto->warehouseId !== '') {
            $qb->andWhere('fa.warehouse = :warehouseId')->setParameter('warehouseId', $searchDto->warehouseId);
        }

        if ($searchDto->name !== null && $searchDto->name !== '') {
            $qb->andWhere('LOWER(fa.name) LIKE LOWER(:name)')->setParameter('name', '%' . $searchDto->name . '%');
        }

        if ($searchDto->serialNumber !== null && $searchDto->serialNumber !== '') {
            $qb->andWhere('LOWER(fa.serialNumber) LIKE LOWER(:serialNumber)')->setParameter('serialNumber', '%' . $searchDto->serialNumber . '%');
        }

        if ($searchDto->status !== null) {
            $qb->andWhere('fa.status = :status')->setParameter('status', $searchDto->status);
        }
    }

    protected function getModelClassName(): string
    {
        return FixedAsset::class;
    }
}
