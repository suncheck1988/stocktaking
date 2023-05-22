<?php

declare(strict_types=1);

namespace App\Store\Model\Warehouse;

use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Exception\Warehouse\WarehouseAlreadyExistException;
use App\Store\Specification\Warehouse\UniqueWarehouseNameSpecification;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"warehouse"')]
class Warehouse
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'store_warehouse_status')]
    private Status $status;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __construct(
        Uuid $id,
        Client $client,
        string $name,
        DateTimeImmutable $date,
        UniqueWarehouseNameSpecification $uniqueWarehouseNameSpecification
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->name = $name;
        $this->status = Status::active();

        $this->createdAt = $date;

        if ($uniqueWarehouseNameSpecification->isSatisfiedBy($this) === false) {
            throw new WarehouseAlreadyExistException($this->name);
        }
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

    public function update(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
