<?php

declare(strict_types=1);

namespace App\Order\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Order\Model\DeliveryType\DeliveryType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

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
    public function count(): int
    {
        $qb = $this->entityRepository->createQueryBuilder('dt');

        $qb->select('COUNT(dt) as dtCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function get(Uuid $id, Client $client): DeliveryType
    {
        /** @var DeliveryType|null $model */
        $model = $this->entityRepository->findBy(['id' => $id, 'client' => $client]);
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
            ->setParameter('client', $deliveryType->getClient())
            ->setParameter('name', $deliveryType->getName());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return DeliveryType[]
     */
    public function fetchAll(
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('dt');

        $qb->orderBy('dt.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var DeliveryType[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    protected function getModelClassName(): string
    {
        return DeliveryType::class;
    }
}
