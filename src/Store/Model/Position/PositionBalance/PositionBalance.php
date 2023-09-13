<?php

declare(strict_types=1);

namespace App\Store\Model\Position\PositionBalance;

use App\Application\Exception\DomainException;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Store\Exception\Position\PositionBalanceAlreadyExistException;
use App\Store\Model\Position\Position;
use App\Store\Model\Warehouse\Warehouse;
use App\Store\Specification\Position\UniquePositionBalanceWarehouseSpecification;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"position_balance"')]
class PositionBalance
{
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: Position::class, inversedBy: 'positionBalances')]
    #[ORM\JoinColumn(name: 'position_id', referencedColumnName: 'id', nullable: false)]
    private Position $position;

    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    #[ORM\JoinColumn(name: 'warehouse_id', referencedColumnName: 'id', nullable: false)]
    private Warehouse $warehouse;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'store_position_balance_status')]
    private Status $status;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __construct(
        Uuid $id,
        Position $position,
        Warehouse $warehouse,
        int $quantity,
        DateTimeImmutable $date,
        UniquePositionBalanceWarehouseSpecification $uniquePositionBalanceWarehouseSpecification
    ) {
        $this->id = $id;
        $this->position = $position;
        $this->warehouse = $warehouse;
        $this->quantity = $quantity;
        $this->status = Status::active();

        $this->createdAt = $date;

        if ($uniquePositionBalanceWarehouseSpecification->isSatisfiedBy($this->position, $this->warehouse) === false) {
            throw new PositionBalanceAlreadyExistException($this->position, $this->warehouse);
        }

        if ($warehouse->getStatus()->isInactive()) {
            throw new DomainException('Склад должен быть активен');
        }
    }

    public function update(int $quantity): void
    {
        if ($this->quantity !== $quantity) {
            $this->quantity = $quantity;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function inactive(): void
    {
        /** @todo нельзя деактивировать, если используется в активных транзакциях */
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getWarehouse(): Warehouse
    {
        return $this->warehouse;
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
