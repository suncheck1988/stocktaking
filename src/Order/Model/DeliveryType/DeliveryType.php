<?php

declare(strict_types=1);

namespace App\Order\Model\DeliveryType;

use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Order\Exception\DeliveryType\DeliveryTypeAlreadyExistException;
use App\Order\Specification\DeliveryType\UniqueDeliveryTypeNameSpecification;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"delivery_type"')]
class DeliveryType
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'order_delivery_type_status')]
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
        UniqueDeliveryTypeNameSpecification $uniqueDeliveryTypeNameSpecification
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->name = $name;
        $this->status = Status::active();

        $this->createdAt = $date;

        if ($uniqueDeliveryTypeNameSpecification->isSatisfiedBy($this) === false) {
            throw new DeliveryTypeAlreadyExistException($this->name);
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

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function update(string $name, UniqueDeliveryTypeNameSpecification $uniqueDeliveryTypeNameSpecification): void
    {
        if ($uniqueDeliveryTypeNameSpecification->isSatisfiedBy($this) === false) {
            throw new DeliveryTypeAlreadyExistException($this->name);
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
