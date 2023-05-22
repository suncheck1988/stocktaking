<?php

declare(strict_types=1);

namespace App\Order\Model\Order;

use App\Application\Exception\DomainException;
use App\Application\Exception\InvalidStatusTransitionException;
use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Amount;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\User;
use App\Client\Model\Client\Client;
use App\Client\Model\Counterparty\Counterparty;
use App\Order\Model\DeliveryType\DeliveryType;
use App\Order\Model\Order\OrderItem\OrderItem;
use App\Order\Model\Order\OrderItem\Status as OrderItemStatus;
use App\Order\Model\PaymentMethod\PaymentMethod;
use App\Order\Specification\Order\UniqueOrderNumberSpecification;
use App\Store\Model\Position\Position;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '"order"')]
class Order
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'responsible_user_id', referencedColumnName: 'id', nullable: false)]
    private User $responsibleUser;

    #[ORM\ManyToOne(targetEntity: Counterparty::class)]
    #[ORM\JoinColumn(name: 'counterparty_id', referencedColumnName: 'id')]
    private ?Counterparty $counterparty;

    #[ORM\ManyToOne(targetEntity: PaymentMethod::class)]
    #[ORM\JoinColumn(name: 'payment_method_id', referencedColumnName: 'id', nullable: false)]
    private PaymentMethod $paymentMethod;

    #[ORM\ManyToOne(targetEntity: DeliveryType::class)]
    #[ORM\JoinColumn(name: 'delivery_type_id', referencedColumnName: 'id', nullable: false)]
    private DeliveryType $deliveryType;

    #[ORM\Column(type: 'integer')]
    private int $number;

    #[ORM\Column(type: 'string')]
    private string $address;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $comment;

    #[ORM\Column(type: 'amount')]
    private Amount $totalPrice;

    #[ORM\Column(type: 'amount')]
    private Amount $deliveryPrice;

    #[ORM\Column(type: 'integer')]
    private int $totalQuantity;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['all'])]
    private Collection $orderItems;

    #[ORM\Column(type: 'order_order_status')]
    private Status $status;

    /**
     * @throws Exception
     */
    public function __construct(
        Uuid $id,
        Client $client,
        User $responsibleUser,
        ?Counterparty $counterparty,
        PaymentMethod $paymentMethod,
        DeliveryType $deliveryType,
        string $address,
        ?string $comment,
        ?Amount $deliveryPrice,
        DateTimeImmutable $date,
        UniqueOrderNumberSpecification $uniqueOrderNumberSpecification
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->responsibleUser = $responsibleUser;
        $this->counterparty = $counterparty;
        $this->paymentMethod = $paymentMethod;
        $this->deliveryType = $deliveryType;
        $this->number = $uniqueOrderNumberSpecification->generate($client);
        $this->address = $address;
        $this->comment = $comment;
        $this->totalPrice = new Amount(0);
        $this->deliveryPrice = $deliveryPrice !== null ? $deliveryPrice : new Amount(0);
        $this->totalQuantity = 0;
        $this->orderItems = new ArrayCollection();
        $this->status = Status::new();

        $this->createdAt = $date;

        if (!$responsibleUser->getStatus()->isActive()) {
            throw new DomainException('Ответственный сотрудник должен быть активен');
        }

        if ($counterparty!== null && !$counterparty->getStatus()->isActive()) {
            throw new DomainException('Контрагент должен быть активен');
        }

        if (!$paymentMethod->getStatus()->isActive()) {
            throw new DomainException('Метод оплаты должен быть активен');
        }

        if (!$deliveryType->getStatus()->isActive()) {
            throw new DomainException('Тип доставки должен быть активен');
        }
    }

    public function addOrderItem(
        Uuid $id,
        Position $position,
        Amount $price,
        int $quantity,
        DateTimeImmutable $date
    ): OrderItem {
        $orderItem = new OrderItem(
            $id,
            $this,
            $position,
            $price,
            $quantity,
            $date
        );

        $this->orderItems->add($orderItem);

        $this->totalPrice = new Amount($this->totalPrice->getValue() + $orderItem->getQuantity());
        $this->totalQuantity = $this->totalQuantity + $orderItem->getQuantity();

        return $orderItem;
    }

    public function recalculateTotalAmount(): void
    {
        $totalPrice = 0;
        $totalQuantity = 0;
        foreach ($this->getOrderItems() as $orderItem) {
            if (
                \in_array(
                    $orderItem->getStatus()->getValue(),
                    [OrderItemStatus::CONFIRMED, OrderItemStatus::CHANGED, OrderItemStatus::ADDED],
                    true
                )
            ) {
                $totalPrice += ($orderItem->getPrice()->getValue() * $orderItem->getQuantity());
                $totalQuantity += $orderItem->getQuantity();
            }
        }

        $this->totalPrice = new Amount($totalPrice);
        $this->totalQuantity = $totalQuantity;
    }

    public function confirmed(): void
    {
        if (!$this->status->isNew()) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Invalid status transition from %s to %s for order %s',
                    $this->status->getName(),
                    'CONFIRMED',
                    $this->getId()->getValue()
                ),
            );
        }

        $this->status = Status::confirmed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function processed(): void
    {
        if (!$this->status->isConfirmed()) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Invalid status transition from %s to %s for order %s',
                    $this->status->getName(),
                    'PROCESSED',
                    $this->getId()->getValue()
                ),
            );
        }

        $this->status = Status::processed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function sent(): void
    {
        if (!$this->status->isProcessed()) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Invalid status transition from %s to %s for order %s',
                    $this->status->getName(),
                    'SENT',
                    $this->getId()->getValue()
                ),
            );
        }

        $this->status = Status::processed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function done(): void
    {
        if (!$this->status->isSent()) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Invalid status transition from %s to %s for order %s',
                    $this->status->getName(),
                    'DONE',
                    $this->getId()->getValue()
                ),
            );
        }

        $this->status = Status::done();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function canceled(): void
    {
        if (!$this->status->isNew()) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Invalid status transition from %s to %s for order %s',
                    $this->status->getName(),
                    'CANCELED',
                    $this->getId()->getValue()
                ),
            );
        }

        $this->status = Status::canceled();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getResponsibleUser(): User
    {
        return $this->responsibleUser;
    }

    public function getCounterparty(): ?Counterparty
    {
        return $this->counterparty;
    }

    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function getDeliveryType(): DeliveryType
    {
        return $this->deliveryType;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getTotalPrice(): Amount
    {
        return $this->totalPrice;
    }

    public function getDeliveryPrice(): Amount
    {
        return $this->deliveryPrice;
    }

    public function getTotalQuantity(): int
    {
        return $this->totalQuantity;
    }

    /**
     * @return OrderItem[]
     */
    public function getOrderItems(): array
    {
        /** @var OrderItem[] $result */
        $result = $this->orderItems->toArray();

        return $result;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
