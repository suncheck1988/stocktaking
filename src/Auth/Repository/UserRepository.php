<?php

declare(strict_types=1);

namespace App\Auth\Repository;

use App\Application\Dto\PaginationDto;
use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\ValueObject\Uuid;
use App\Auth\Dto\UserSearchDto;
use App\Auth\Model\User\Email;
use App\Auth\Model\User\Status;
use App\Auth\Model\User\User;
use App\Auth\Model\User\UserEmailConfirm\Status as UserEmailConfirmStatus;
use App\Auth\Model\User\UserEmailConfirm\Type;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class UserRepository extends AbstractRepository
{
    public function add(User $user): void
    {
        $this->entityManager->persist($user);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function count(?UserSearchDto $searchDto = null): int
    {
        $qb = $this->entityRepository->createQueryBuilder('u');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->select('COUNT(u) as uCount');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return User[]
     */
    public function fetchAll(
        ?UserSearchDto $searchDto = null,
        ?PaginationDto $paginationDto = null
    ): array {
        $qb = $this->entityRepository->createQueryBuilder('u');

        if ($searchDto !== null) {
            $this->applySearchDto($qb, $searchDto);
        }

        $qb->orderBy('u.createdAt', 'DESC');

        if ($paginationDto !== null) {
            $qb->setFirstResult($paginationDto->getOffset());
            $qb->setMaxResults($paginationDto->getLimit());
        }

        /** @var User[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function get(Uuid $id): User
    {
        /** @var User|null $model */
        $model = $this->entityRepository->find($id);
        if ($model === null) {
            throw new NotFoundException(sprintf('User with id %s not found', $id->getValue()));
        }

        return $model;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getByEmail(Email $email): User
    {
        /** @var User|null $model */
        $model = $this->entityRepository
            ->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email->getValue())
            ->getQuery()
            ->getOneOrNullResult();

        if ($model === null) {
            throw new NotFoundException(sprintf('Пользователь с электронной почтой %s не найден', $email->getValue()));
        }

        return $model;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function existByEmail(Email $email, ?Uuid $excludeId = null): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('LOWER(u.email) = :email')
            ->setParameter('email', mb_strtolower((string)$email));

        if ($excludeId !== null) {
            $qb->andWhere('u.id != :excludeId')->setParameter('excludeId', $excludeId);
        }

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function existByActualEmailConfirm(User $user): bool
    {
        $qb = $this->entityRepository->createQueryBuilder('u')
            ->leftJoin('u.userEmailConfirms', 'uec')
            ->select('COUNT(u)')
            ->where('u = :user')
            ->andWhere('uec.type = :registrationType')
            ->andWhere('uec.status = :newStatus')
            ->andWhere('uec.expirationDate < :currentDateTime')
            ->setParameter('user', $user)
            ->setParameter('registrationType', Type::REGISTRATION)
            ->setParameter('newStatus', UserEmailConfirmStatus::NEW)
            ->setParameter('currentDateTime', new DateTimeImmutable());

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getActiveByEmail(Email $email): User
    {
        $qb = $this->entityRepository->createQueryBuilder('u')
            ->where('u.email = :email')
            ->andWhere('u.status = :activeStatus')
            ->setParameter('email', $email->getValue())
            ->setParameter('activeStatus', Status::ACTIVE)
            ->setMaxResults(1);

        /** @var User|null $model */
        $model = $qb->getQuery()->getOneOrNullResult();
        if ($model === null) {
            throw new NotFoundException('Активный пользователь с указанным email не найден');
        }

        return $model;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getNewByEmailConfirmToken(Uuid $token): User
    {
        $qb = $this->entityRepository->createQueryBuilder('u')
            ->innerJoin('u.userEmailConfirms', 'uec')
            ->where('u.status = :newStatus')
            ->andWhere('uec.token = :token')
            ->setParameter('newStatus', Status::NEW)
            ->setParameter('token', $token->getValue())
            ->setMaxResults(1);

        /** @var User|null $model */
        $model = $qb->getQuery()->getOneOrNullResult();
        if ($model === null) {
            throw new NotFoundException('Пользователь, требующий подтверждения не найден');
        }

        return $model;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getActiveByEmailConfirmToken(Uuid $token): User
    {
        $qb = $this->entityRepository->createQueryBuilder('u')
            ->innerJoin('u.userEmailConfirms', 'uec')
            ->where('u.status = :activeStatus')
            ->andWhere('uec.token = :token')
            ->setParameter('activeStatus', Status::ACTIVE)
            ->setParameter('token', $token->getValue())
            ->setMaxResults(1);

        /** @var User|null $model */
        $model = $qb->getQuery()->getOneOrNullResult();
        if ($model === null) {
            throw new NotFoundException('Пользователь, требующий подтверждения не найден');
        }

        return $model;
    }

    private function applySearchDto(QueryBuilder $qb, UserSearchDto $searchDto): void
    {
        if ($searchDto->id !== null && $searchDto->id !== '') {
            $qb->andWhere('u.id = :id')->setParameter('id', $searchDto->id);
        }
    }

    protected function getModelClassName(): string
    {
        return User::class;
    }
}
