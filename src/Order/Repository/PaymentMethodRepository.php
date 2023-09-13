<?php

declare(strict_types=1);

namespace App\Order\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Order\Dto\PaymentMethodSearchDto;
use App\Order\Model\PaymentMethod\PaymentMethod;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class PaymentMethodRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(PaymentMethod $paymentMethod): void
    {
        $this->entityManager->persist($paymentMethod);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(Client $client, ?PaymentMethodSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('pm');

        $qb->where('pm.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(pm) as pmCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(Uuid $id, Client $client): PaymentMethod
    {
        /** @var PaymentMethod|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('pm')
            ->where('pm.id = :id')
            ->andWhere('pm.client = :client')
            ->setParameter('id', $id->getValue())
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();

        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Тип оплаты с id %s, клиента %s не найдена',
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
    public function existByName(PaymentMethod $paymentMethod): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('pm')
            ->select('COUNT(pm)')
            ->where('pm.client = :client')
            ->andWhere('LOWER(pm.name) = LOWER(:name)')
            ->andWhere('pm.id <> :id')
            ->setParameter('client', $paymentMethod->getClient())
            ->setParameter('name', $paymentMethod->getName())
            ->setParameter('id', $paymentMethod->getId()->getValue());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return PaymentMethod[]
     */
    public function fetchAll(
        Client $client,
        ?PaymentMethodSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('pm');

        $qb->where('pm.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('pm.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var PaymentMethod[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, PaymentMethodSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('pm.id = :id')->setParameter('id', $searchDto->id);
        }

        if ($searchDto->name !== null && $searchDto->name !== '') {
            $qb->andWhere('LOWER(pm.name) LIKE LOWER(:name)')->setParameter('name', '%' . $searchDto->name . '%');
        }

        if ($searchDto->status !== null) {
            $qb->andWhere('pm.status = :status')->setParameter('status', $searchDto->status);
        }
    }

    protected function getModelClassName(): string
    {
        return PaymentMethod::class;
    }
}
