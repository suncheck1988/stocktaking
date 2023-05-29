<?php

declare(strict_types=1);

namespace App\Auth\Model\User;

use App\Application\Exception\Code\ExceptionCodeEnum;
use App\Application\Exception\DomainException;
use App\Application\Exception\InvalidStatusTransitionException;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Auth\Exception\Auth\ClientEmailConfirmAlreadyRequested;
use App\Auth\Exception\EmailAlreadyExistException;
use App\Auth\Model\User\UserAuth\UserAuth;
use App\Auth\Model\User\UserEmailConfirm\Status as UserEmailConfirmStatus;
use App\Auth\Model\User\UserEmailConfirm\Type as UserEmailConfirmType;
use App\Auth\Model\User\UserEmailConfirm\UserEmailConfirm;
use App\Auth\Specification\Auth\ClientRegistrationEmailConfirmSpecification;
use App\Auth\Specification\UniqueUserEmailSpecification;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"user"')]
class User
{
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'auth_user_email')]
    private Email $email;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isEmailConfirmed;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserAuth::class, cascade: ['all'])]
    private ?UserAuth $userAuth = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserEmailConfirm::class, cascade: ['all'])]
    private Collection $userEmailConfirms;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserPermission::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $userPermissions;

    #[ORM\Column(type: 'auth_user_role')]
    private Role $role;

    #[ORM\Column(type: 'auth_user_status')]
    private Status $status;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __construct(
        Uuid $id,
        string $name,
        Email $email,
        Role $role,
        DateTimeImmutable $date,
        ?UniqueUserEmailSpecification $uniqueUserEmailSpecification
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->isEmailConfirmed = false;
        $this->role = $role;
        $this->status = Status::new();

        $this->userEmailConfirms = new ArrayCollection();
        $this->userPermissions = new ArrayCollection();

        $this->createdAt = $date;

        if ($uniqueUserEmailSpecification !== null && $uniqueUserEmailSpecification->isSatisfiedBy($this) === false) {
            throw new EmailAlreadyExistException((string)$this->email);
        }
    }

    public function createUserAuth(
        Uuid $id,
        string $hash,
        DateTimeImmutable $date,
    ): UserAuth {
        if ($this->userAuth !== null) {
            throw new DomainException('Пользователь уже зарегистрирован');
        }

        $userAuth = new UserAuth($id, $this, $hash, $date);

        $this->userAuth = $userAuth;

        return $userAuth;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function addUserEmailConfirm(
        Uuid $id,
        Uuid $token,
        UserEmailConfirmType $type,
        DateTimeImmutable $date,
        ClientRegistrationEmailConfirmSpecification $clientRegistrationEmailConfirmSpecification
    ): UserEmailConfirm {
        if ($clientRegistrationEmailConfirmSpecification->isEmailConfirmRequested($this) === false) {
            throw new ClientEmailConfirmAlreadyRequested();
        }

        $userEmailConfirm = new UserEmailConfirm($id, $this, $token, $type, $date);

        $this->userEmailConfirms->add($userEmailConfirm);

        return $userEmailConfirm;
    }

    public function addUserPermission(Uuid $id, Permission $permission): UserPermission
    {
        $userPermission = new UserPermission($id, $this, $permission);
        $this->userPermissions->add($userPermission);

        return $userPermission;
    }

    public function removeUserPermission(UserPermission $userPermission): void
    {
        $this->userPermissions->removeElement($userPermission);
    }

    public function hasPermission(Permission $permission): bool
    {
        foreach ($this->getUserPermissions() as $userPermission) {
            if ($userPermission->getPermission()->isEqualTo($permission)) {
                return true;
            }
        }

        return false;
    }

    public function active(): void
    {
        $this->status = Status::active();
        $this->isEmailConfirmed = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function block(): void
    {
        if (!$this->status->isActive()) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Invalid status transition from %s to %s for user %s',
                    $this->status->getName(),
                    'BLOCKED',
                    $this->getId()->getValue()
                ),
            );
        }

        $this->status = Status::blocked();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function checkPassword(string $password): void
    {
        $userAuth = $this->getUserAuth();
        if ($userAuth === null) {
            throw new DomainException('Пользователь не зарегистрирован');
        }

        if (!password_verify($password, $userAuth->getHash())) {
            throw new DomainException('Не верный пароль', ExceptionCodeEnum::PASSWORD_INCORRECT->value);
        }
    }

    public function changePassword(string $hash): void
    {
        $userAuth = $this->getUserAuth();
        if ($userAuth === null) {
            throw new DomainException('Пользователь не зарегистрирован');
        }

        $userAuth->changeHash($hash);
    }

    public function update(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function isEmailConfirmed(): bool
    {
        return $this->isEmailConfirmed;
    }

    public function getUserAuth(): ?UserAuth
    {
        return $this->userAuth;
    }

    /**
     * @return UserEmailConfirm[]
     */
    public function getUserEmailConfirms(): array
    {
        /** @var UserEmailConfirm[] $result */
        $result = $this->userEmailConfirms->toArray();

        return $result;
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function getUserEmailConfirmByToken(Uuid $token, UserEmailConfirmType $type): ?UserEmailConfirm
    {
        $criteria = new Criteria();

        $expr1 = new Comparison('token', Comparison::EQ, $token->getValue());
        $expr2 = new Comparison('type', Comparison::EQ, $type->getValue());

        $criteria->where($expr1)->andWhere($expr2)->setMaxResults(1);

        /** @var UserEmailConfirm|null $result */
        $result = $this->userEmailConfirms->matching($criteria)[0];

        return $result;
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function getUserEmailConfirmByStatus(UserEmailConfirmStatus $status, UserEmailConfirmType $type): ?UserEmailConfirm
    {
        $criteria = new Criteria();

        $expr1 = new Comparison('status', Comparison::EQ, $status->getValue());
        $expr2 = new Comparison('type', Comparison::EQ, $type->getValue());

        $criteria->where($expr1)->andWhere($expr2)->setMaxResults(1);

        /** @var UserEmailConfirm|null $result */
        $result = $this->userEmailConfirms->matching($criteria)[0];

        return $result;
    }

    /**
     * @return UserPermission[]
     */
    public function getUserPermissions(): array
    {
        /** @var UserPermission[] $result */
        $result = $this->userPermissions->toArray();

        return $result;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
