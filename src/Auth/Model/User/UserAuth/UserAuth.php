<?php

declare(strict_types=1);

namespace App\Auth\Model\User\UserAuth;

use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_auth')]
class UserAuth
{
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\OneToOne(inversedBy: 'userAuth', targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string')]
    private string $hash;

    public function __construct(
        Uuid $id,
        User $user,
        string $hash,
        DateTimeImmutable $date,
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->hash = $hash;

        $this->createdAt = $date;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function changeHash(string $hash): void
    {
        $this->hash = $hash;
    }
}
