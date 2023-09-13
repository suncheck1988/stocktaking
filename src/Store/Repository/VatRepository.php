<?php

declare(strict_types=1);

namespace App\Store\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Dto\VatSearchDto;
use App\Store\Model\Vat\Status;
use App\Store\Model\Vat\Vat;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class VatRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(Vat $vat): void
    {
        $this->entityManager->persist($vat);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(Client $client, ?VatSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('v');

        $qb->where('v.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(v) as vCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(Uuid $id, Client $client): Vat
    {
        /** @var Vat|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('v')
            ->where('v.id = :id')
            ->andWhere('v.client = :client')
            ->setParameter('id', $id->getValue())
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();

        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Ставка НДС с id %s, клиента %s не найдена',
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
    public function existByValue(Vat $vat): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('v')
            ->select('COUNT(v)')
            ->where('v.client = :client')
            ->andWhere('v.value = :value')
            ->setParameter('client', $vat->getClient())
            ->setParameter('value', $vat->getValue());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function existDefault(Vat $vat): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('v')
            ->select('COUNT(v)')
            ->where('v.client = :client')
            ->andWhere('v.isDefault = true')
            ->andWhere('v.status = :activeStatus')
            ->setParameter('client', $vat->getClient())
            ->setParameter('activeStatus', Status::ACTIVE);

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return Vat[]
     */
    public function fetchAll(Client $client, ?VatSearchDto $searchDto = null, ?PaginationDto $paginationDto = null): array
    {
        $qb = $this->entityRepository->createQueryBuilder('v');

        $qb->where('v.client = :client')
            ->setParameter('client', $client);

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('v.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var Vat[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, VatSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('v.id = :id')->setParameter('id', $searchDto->id);
        }

        if ($searchDto->isDefault) {
            $qb->andWhere('v.isDefault = true');
        }

        if ($searchDto->status !== null) {
            $qb->andWhere('v.status = :status')->setParameter('status', $searchDto->status);
        }
    }

    protected function getModelClassName(): string
    {
        return Vat::class;
    }
}
