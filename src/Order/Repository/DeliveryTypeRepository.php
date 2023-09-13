<?php

declare(strict_types=1);

namespace App\Order\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Order\Dto\DeliveryTypeSearchDto;
use App\Order\Model\DeliveryType\DeliveryType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class DeliveryTypeRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(DeliveryType $deliveryType): void
    {
        $this->entityManager->persist($deliveryType);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(Client $client, ?DeliveryTypeSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('dt');

        $qb->where('dt.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(dt) as dtCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(Uuid $id, Client $client): DeliveryType
    {
        /** @var DeliveryType|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('dt')
            ->where('dt.id = :id')
            ->andWhere('dt.client = :client')
            ->setParameter('id', $id->getValue())
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();

        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Тип доставки с id %s, клиента %s не найдена',
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
    public function existByName(DeliveryType $deliveryType): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('dt')
            ->select('COUNT(dt)')
            ->where('dt.client = :client')
            ->andWhere('LOWER(dt.name) = LOWER(:name)')
            ->andWhere('dt.id <> :id')
            ->setParameter('client', $deliveryType->getClient())
            ->setParameter('name', $deliveryType->getName())
            ->setParameter('id', $deliveryType->getId()->getValue());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return DeliveryType[]
     */
    public function fetchAll(
        Client $client,
        ?DeliveryTypeSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('dt');

        $qb->where('dt.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('dt.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var DeliveryType[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, DeliveryTypeSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('dt.id = :id')->setParameter('id', $searchDto->id);
        }

        if ($searchDto->name !== null && $searchDto->name !== '') {
            $qb->andWhere('LOWER(dt.name) LIKE LOWER(:name)')->setParameter('name', '%' . $searchDto->name . '%');
        }

        if ($searchDto->status !== null) {
            $qb->andWhere('dt.status = :status')->setParameter('status', $searchDto->status);
        }
    }

    protected function getModelClassName(): string
    {
        return DeliveryType::class;
    }
}
