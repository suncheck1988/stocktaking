<?php

declare(strict_types=1);

namespace App\Auth\Model\User;

use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '"user_permission"')]
class UserPermission
{
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userPermissions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(type: 'auth_user_permission')]
    private Permission $permission;

    public function __construct(Uuid $id, User $user, Permission $permission)
    {
        $this->id = $id;
        $this->user = $user;
        $this->permission = $permission;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPermission(): Permission
    {
        return $this->permission;
    }
}
