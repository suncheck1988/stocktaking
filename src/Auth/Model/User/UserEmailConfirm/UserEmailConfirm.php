<?php

declare(strict_types=1);

namespace App\Auth\Model\User\UserEmailConfirm;

use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_email_confirm')]
class UserEmailConfirm
{
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userEmailConfirms')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(type: 'uuid')]
    private Uuid $token;

    #[ORM\Column(type: 'auth_user_email_confirm_type')]
    private Type $type;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $expirationDate;

    #[ORM\Column(type: 'auth_user_email_confirm_status')]
    private Status $status;

    public function __construct(
        Uuid $id,
        User $user,
        Uuid $token,
        Type $type,
        DateTimeImmutable $date
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->token = $token;
        $this->type = $type;
        $this->expirationDate = (new DateTimeImmutable())->add(new DateInterval('P1D'));
        $this->status = Status::new();

        $this->createdAt = $date;
    }

    public function confirmed(): void
    {
        $this->status = Status::confirmed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function expired(): void
    {
        $this->status = Status::expired();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isExpired(): bool
    {
        return new DateTimeImmutable() > $this->expirationDate;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getToken(): Uuid
    {
        return $this->token;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getExpirationDate(): DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
