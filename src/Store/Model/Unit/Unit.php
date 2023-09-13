<?php

declare(strict_types=1);

namespace App\Store\Model\Unit;

use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Exception\Unit\UnitAlreadyExistException;
use App\Store\Exception\Unit\UnitInUseException;
use App\Store\Specification\Unit\NotUsedUnitSpecification;
use App\Store\Specification\Unit\UniqueUnitNameSpecification;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"unit"')]
class Unit
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'store_unit_status')]
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
        UniqueUnitNameSpecification $uniqueUnitNameSpecification
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->name = $name;
        $this->status = Status::active();

        $this->createdAt = $date;

        if ($uniqueUnitNameSpecification->isSatisfiedBy($this) === false) {
            throw new UnitAlreadyExistException($this->name);
        }
    }

    public function active(): void
    {
        $this->status = Status::active();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function inactive(NotUsedUnitSpecification $notUsedUnitSpecification): void
    {
        if ($notUsedUnitSpecification->isSatisfiedBy($this) === false) {
            throw new UnitInUseException($this->name);
        }

        $this->status = Status::inactive();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function update(string $name, UniqueUnitNameSpecification $uniqueUnitNameSpecification): void
    {
        if ($uniqueUnitNameSpecification->isSatisfiedBy($this) === false) {
            throw new UnitAlreadyExistException($this->name);
        }

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
