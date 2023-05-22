<?php

declare(strict_types=1);

namespace App\Store\Repository;

use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Dto\VatSearchDto;
use App\Store\Model\Vat\Status;
use App\Store\Model\Vat\Vat;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class VatRepository extends AbstractRepository
{
    public function add(Vat $vat): void
    {
        $this->entityManager->persist($vat);
    }

    public function remove(Vat $vat): void
    {
        $this->entityManager->remove($vat);
    }

    public function get(Uuid $id, Client $client): Vat
    {
        /** @var Vat|null $model */
        $model = $this->entityRepository->findBy(['id' => $id, 'client' => $client]);
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
            ->andWhere('LOWER(v.value) = LOWER(:value)')
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
    public function fetchAll(?VatSearchDto $searchDto = null): array
    {
        $qb = $this->entityRepository->createQueryBuilder('v');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('v.createdAt', 'DESC');

        /** @var Vat[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function applySearchDto(QueryBuilder $qb, VatSearchDto $searchDto): void
    {
        if ($searchDto->clientId !== null && $searchDto->clientId !== '') {
            $qb->andWhere('v.client = :clientId')->setParameter('clientId', $searchDto->clientId);
        }

        if ($searchDto->isDefault) {
            $qb->andWhere('v.isDefault = true');
        }
    }

    protected function getModelClassName(): string
    {
        return Vat::class;
    }
}
