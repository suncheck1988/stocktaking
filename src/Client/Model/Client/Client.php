<?php

declare(strict_types=1);

namespace App\Client\Model\Client;

use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Auth\Model\User\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '"client"')]
class Client
{
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    public function __construct(
        User $user,
        DateTimeImmutable $date
    ) {
        $this->id = $user->getId();
        $this->user = $user;

        $this->createdAt = $date;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
