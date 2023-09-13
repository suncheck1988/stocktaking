<?php

declare(strict_types=1);

namespace App\Client\Model\Counterparty;

use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Email;
use App\Client\Model\Client\Client;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '"counterparty"')]
class Counterparty
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'auth_user_email', nullable: true)]
    private ?Email $email;

    #[ORM\Column(type: 'client_counterparty_status')]
    private Status $status;

    public function __construct(
        Uuid $id,
        Client $client,
        string $name,
        ?Email $email,
        DateTimeImmutable $date
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->name = $name;
        $this->email = $email;
        $this->status = Status::active();

        $this->createdAt = $date;
    }

    public function active(): void
    {
        $this->status = Status::active();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function inactive(): void
    {
        $this->status = Status::inactive();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function update(string $name, ?Email $email): void
    {
        $this->name = $name;
        $this->email = $email;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
