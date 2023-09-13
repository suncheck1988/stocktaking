<?php

declare(strict_types=1);

namespace App\Store\Model\Vat;

use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Exception\Vat\VatAlreadyExistException;
use App\Store\Specification\Vat\DefaultVatSpecification;
use App\Store\Specification\Vat\UniqueVatValueSpecification;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"vat"')]
class Vat
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\Column(type: 'integer')]
    private int $value;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDefault;

    #[ORM\Column(type: 'store_vat_status')]
    private Status $status;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __construct(
        Uuid $id,
        Client $client,
        int $value,
        DateTimeImmutable $date,
        UniqueVatValueSpecification $uniqueVatValueSpecification,
        DefaultVatSpecification $defaultVatSpecification
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->value = $value;
        $this->isDefault = $defaultVatSpecification->isExistDefault($this);
        $this->status = Status::active();

        $this->createdAt = $date;

        if ($uniqueVatValueSpecification->isSatisfiedBy($this) === false) {
            throw new VatAlreadyExistException($this->value);
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

    public function update(int $value): void
    {
        $this->value = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
