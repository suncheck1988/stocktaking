<?php

declare(strict_types=1);

namespace App\Order\Model\PaymentMethod;

use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Order\Exception\PaymentMethod\PaymentMethodAlreadyExistException;
use App\Order\Specification\PaymentMethod\UniquePaymentMethodNameSpecification;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"payment_method"')]
class PaymentMethod
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'order_payment_method_status')]
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
        UniquePaymentMethodNameSpecification $uniquePaymentMethodNameSpecification
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->name = $name;
        $this->status = Status::active();

        $this->createdAt = $date;

        if ($uniquePaymentMethodNameSpecification->isSatisfiedBy($this) === false) {
            throw new PaymentMethodAlreadyExistException($this->name);
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
    public function update(string $name, UniquePaymentMethodNameSpecification $uniquePaymentMethodNameSpecification): void
    {
        if ($uniquePaymentMethodNameSpecification->isSatisfiedBy($this) === false) {
            throw new PaymentMethodAlreadyExistException($this->name);
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
