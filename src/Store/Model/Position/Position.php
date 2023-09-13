<?php

declare(strict_types=1);

namespace App\Store\Model\Position;

use App\Application\Exception\DomainException;
use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Amount;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Exception\Position\PositionAlreadyExistException;
use App\Store\Model\Category\Category;
use App\Store\Model\Position\PositionBalance\PositionBalance;
use App\Store\Model\Unit\Unit;
use App\Store\Model\Vat\Vat;
use App\Store\Model\Warehouse\Warehouse;
use App\Store\Specification\Position\UniquePositionBalanceWarehouseSpecification;
use App\Store\Specification\Position\UniquePositionNameSpecification;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"position"')]
class Position
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    private Category $category;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'amount')]
    private Amount $price;

    #[ORM\ManyToOne(targetEntity: Vat::class)]
    #[ORM\JoinColumn(name: 'vat_id', referencedColumnName: 'id')]
    private ?Vat $vat;

    #[ORM\ManyToOne(targetEntity: Unit::class)]
    #[ORM\JoinColumn(name: 'unit_id', referencedColumnName: 'id', nullable: false)]
    private Unit $unit;

    #[ORM\OneToMany(mappedBy: 'position', targetEntity: PositionBalance::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $positionBalances;

    #[ORM\Column(type: 'store_position_status')]
    private Status $status;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __construct(
        Uuid $id,
        Client $client,
        Category $category,
        string $name,
        ?string $description,
        Amount $price,
        ?Vat $vat,
        Unit $unit,
        DateTimeImmutable $date,
        UniquePositionNameSpecification $uniquePositionNameSpecification
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->category = $category;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->vat = $vat;
        $this->unit = $unit;
        $this->status = Status::active();

        $this->positionBalances = new ArrayCollection();

        $this->createdAt = $date;

        if ($uniquePositionNameSpecification->isSatisfiedBy($this) === false) {
            throw new PositionAlreadyExistException($this->name);
        }

        if (!$category->getStatus()->isActive()) {
            throw new DomainException('Категория должна быть активна');
        }
        if ($category->getParent() === null) {
            throw new DomainException('Категория должна входить в корневую категорию');
        }
        if (!empty($category->getChildren())) {
            throw new DomainException('Категория не должна быть корневой');
        }

        if ($vat !== null && !$vat->getStatus()->isActive()) {
            throw new DomainException('Ставка НДС должна быть активна');
        }

        if (!$unit->getStatus()->isActive()) {
            throw new DomainException('Единица измерения должна быть активна');
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

    public function update(
        Category $category,
        string $name,
        ?string $description,
        ?Vat $vat,
        Unit $unit
    ): void {
        if ($vat !== null && !$vat->getStatus()->isActive()) {
            throw new DomainException('Ставка НДС должна быть активна');
        }
        if (!$unit->getStatus()->isActive()) {
            throw new DomainException('Единица измерения должна быть активна');
        }

        $this->category = $category;
        $this->name = $name;
        $this->description = $description;
        $this->vat = $vat;
        $this->unit = $unit;

        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePrice(
        Amount $price
    ): void {
        if ($this->price->getValue() !== $price->getValue()) {
            $this->price = $price;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    /**
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function updateBalance(
        Warehouse $warehouse,
        int $quantity,
        UniquePositionBalanceWarehouseSpecification $uniquePositionBalanceWarehouseSpecification
    ): void {
        if ($warehouse->getClient()->getId()->getValue() !== $this->getClient()->getId()->getValue()) {
            throw new DomainException('Указанный склад не принадлежит текущему клиенту');
        }

        $positionBalance = null;

        foreach ($this->getPositionBalances() as $item) {
            if ($item->getWarehouse()->getId()->getValue() === $warehouse->getId()->getValue()) {
                $positionBalance = $item;
                break;
            }
        }

        if ($positionBalance === null) {
            $positionBalance = new PositionBalance(
                Uuid::generate(),
                $this,
                $warehouse,
                $quantity,
                new DateTimeImmutable(),
                $uniquePositionBalanceWarehouseSpecification
            );
            $this->positionBalances->add($positionBalance);
        } else {
            $positionBalance->update($quantity);
        }
    }

    public function removeBalance(Warehouse $warehouse): void
    {
        if ($warehouse->getClient()->getId()->getValue() !== $this->getClient()->getId()->getValue()) {
            throw new DomainException('Указанный склад не принадлежит текущему клиенту');
        }

        $positionBalance = null;

        foreach ($this->getPositionBalances() as $item) {
            if ($item->getWarehouse()->getId()->getValue() === $warehouse->getId()->getValue()) {
                $positionBalance = $item;
                break;
            }
        }

        if ($positionBalance !== null) {
            $this->positionBalances->removeElement($positionBalance);
        }
    }

    public function getBalanceByWarehouse(Warehouse $warehouse): int
    {
        foreach ($this->getPositionBalances() as $positionBalance) {
            if ($positionBalance->getWarehouse() === $warehouse) {
                return $positionBalance->getQuantity();
            }
        }

        return 0;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPrice(): Amount
    {
        return $this->price;
    }

    public function getVat(): ?Vat
    {
        return $this->vat;
    }

    public function getUnit(): Unit
    {
        return $this->unit;
    }

    /**
     * @return PositionBalance[]
     */
    public function getPositionBalances(): array
    {
        /** @var PositionBalance[] $result */
        $result = $this->positionBalances->toArray();

        return $result;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
