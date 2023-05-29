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

    public function get(Uuid $id, Client $client): FixedAsset
    {
        /** @var FixedAsset|null $model */
        $model = $this->entityRepository->findBy(['id' => $id, 'client' => $client]);
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
            ->setParameter('client', $fixedAsset->getClient())
            ->setParameter('serialNumber', $fixedAsset->getSerialNumber());

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
            ->setParameter('client', $fixedAsset->getClient())
            ->setParameter('inventoryNumber', $fixedAsset->getInventoryNumber());

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
    }

    protected function getModelClassName(): string
    {
        return FixedAsset::class;
    }
}
