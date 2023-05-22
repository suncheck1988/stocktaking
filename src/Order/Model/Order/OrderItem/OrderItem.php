<?php

declare(strict_types=1);

namespace App\Order\Model\Order\OrderItem;

use App\Application\Exception\DomainException;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Amount;
use App\Application\ValueObject\Uuid;
use App\Order\Model\Order\Order;
use App\Store\Model\Position\Position;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '"order_item"')]
class OrderItem
{
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderItems')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false)]
    private Order $order;

    #[ORM\ManyToOne(targetEntity: Order::class)]
    #[ORM\JoinColumn(name: 'position_id', referencedColumnName: 'id', nullable: false)]
    private Position $position;

    #[ORM\Column(type: 'amount')]
    private Amount $price;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'order_order_item_status')]
    private Status $status;

    public function __construct(
        Uuid $id,
        Order $order,
        Position $position,
        Amount $price,
        int $quantity,
        DateTimeImmutable $date
    ) {
        $this->id = $id;
        $this->order = $order;
        $this->position = $position;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->status = Status::new();

        $this->createdAt = $date;

        if (!$position->getStatus()->isActive()) {
            throw new DomainException(sprintf('Позиция заказа %s должна быть активна', $position->getName()));
        }

        $isPositionExistsOnWarehouse = false;
        foreach ($position->getPositionBalances() as $positionBalance) {
            if ($positionBalance->getQuantity() > 0) {
                $isPositionExistsOnWarehouse = true;
                break;
            }
        }

        if (!$isPositionExistsOnWarehouse) {
            throw new DomainException(sprintf('Позиция заказа %s отсутствует на складах', $position->getName()));
        }

        if ($quantity <= 0) {
            throw new DomainException(sprintf('Количество позиции заказа %s должно быть больше нуля', $position->getName()));
        }

        if ($price->toCurrency() <= 0) {
            throw new DomainException(sprintf('Стоимость позиции заказа %s должна быть больше нуля', $position->getName()));
        }
    }

    public function confirm(): void
    {
        $this->status = Status::confirmed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function change(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->status = Status::changed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function delete(): void
    {
        $this->status = Status::deleted();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function add(): void
    {
        $this->status = Status::added();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getPrice(): Amount
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
