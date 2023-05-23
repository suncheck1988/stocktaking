<?php

declare(strict_types=1);

namespace App\Client\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\Repository\ClientableRepositoryInterface;
use App\Application\ValueObject\Uuid;
use App\Client\Dto\EmployeeSearchDto;
use App\Client\Model\Client\Client;
use App\Client\Model\Employee\Employee;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class EmployeeRepository extends AbstractRepository implements ClientableRepositoryInterface
{
    public function add(Employee $employee): void
    {
        $this->entityManager->persist($employee);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(?EmployeeSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('em');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(em) as emCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Employee[]
     */
    public function fetchAll(
        ?EmployeeSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('em');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('em.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var Employee[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function get(Uuid $id, Client $client): Employee
    {
        /** @var Employee|null $model */
        $model = $this->entityRepository->findBy(['id' => $id, 'client' => $client]);
        if ($model === null) {
            throw new NotFoundException(
                sprintf(
                    'Сотрудник с id %s, клиента %s не найден',
                    $id->getValue(),
                    $client->getId()->getValue()
                )
            );
        }

        return $model;
    }

    public function getById(Uuid $id): Employee
    {
        /** @var Employee|null $model */
        $model = $this->entityRepository->findBy(['id' => $id]);
        if ($model === null) {
            throw new NotFoundException(sprintf('Сотрудник с id %s не найден', $id->getValue()));
        }

        return $model;
    }

    private function applySearchDto(QueryBuilder $qb, EmployeeSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('em.id = :id')->setParameter('id', $searchDto->id);
        }
    }

    protected function getModelClassName(): string
    {
        return Employee::class;
    }
}
