<?php

declare(strict_types=1);

namespace App\Order\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Order\Dto\OrderSearchDto;
use App\Order\Model\Order\Order;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class OrderRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(Order $order): void
    {
        $this->entityManager->persist($order);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(?OrderSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('o');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(o) as oCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function get(Uuid $id, Client $client): Order
    {
        /** @var Order|null $model */
        $model = $this->entityRepository->findBy(['id' => $id, 'client' => $client]);
        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Заказ с id %s, клиента %s не найдена',
                    $id->getValue(),
                    $client->getId()->getValue()
                )
            );
        }

        return $model;
    }

    /**
     * @return Order[]
     */
    public function fetchAll(
        ?OrderSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('o');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('o.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var Order[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, OrderSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('o.id = :id')->setParameter('id', $searchDto->id);
        }
    }

    protected function getModelClassName(): string
    {
        return Order::class;
    }
}
