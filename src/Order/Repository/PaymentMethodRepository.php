<?php

declare(strict_types=1);

namespace App\Order\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Order\Model\PaymentMethod\PaymentMethod;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class PaymentMethodRepository extends AbstractRepository
{
    public function add(PaymentMethod $paymentMethod): void
    {
        $this->entityManager->persist($paymentMethod);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(): int
    {
        $qb = $this->entityRepository->createQueryBuilder('pm');

        $qb->select('COUNT(pm) as pmCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function get(Uuid $id, Client $client): PaymentMethod
    {
        /** @var PaymentMethod|null $model */
        $model = $this->entityRepository->findBy(['id' => $id, 'client' => $client]);
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
            ->setParameter('client', $paymentMethod->getClient())
            ->setParameter('name', $paymentMethod->getName());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return PaymentMethod[]
     */
    public function fetchAll(
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('pm');

        $qb->orderBy('pm.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var PaymentMethod[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    protected function getModelClassName(): string
    {
        return PaymentMethod::class;
    }
}
