<?php

declare(strict_types=1);

namespace App\Client\Model\Employee;

use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Auth\Model\User\User;
use App\Client\Model\Client\Client;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '"employee"')]
class Employee
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    /** является МОЛ */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isFinanciallyResponsiblePerson;

    public function __construct(
        User $user,
        Client $client,
        bool $isFinanciallyResponsiblePerson,
        DateTimeImmutable $date
    ) {
        $this->id = $user->getId();
        $this->user = $user;
        $this->client = $client;
        $this->isFinanciallyResponsiblePerson = $isFinanciallyResponsiblePerson;

        $this->createdAt = $date;
    }

    public function update(string $name, bool $isFinanciallyResponsiblePerson): void
    {
        $this->user->update($name);
        $this->isFinanciallyResponsiblePerson = $isFinanciallyResponsiblePerson;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isFinanciallyResponsiblePerson(): bool
    {
        return $this->isFinanciallyResponsiblePerson;
    }
}
