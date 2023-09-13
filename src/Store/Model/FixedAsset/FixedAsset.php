<?php

declare(strict_types=1);

namespace App\Store\Model\FixedAsset;

use App\Application\Exception\InvalidStatusTransitionException;
use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Amount;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\User;
use App\Client\Model\Client\Client;
use App\Client\Model\Counterparty\Counterparty;
use App\Client\Model\Employee\Employee;
use App\Store\Model\Category\Category;
use App\Store\Model\Unit\Unit;
use App\Store\Model\Vat\Vat;
use App\Store\Model\Warehouse\Warehouse;
use App\Store\Specification\FixedAsset\FixedAssetSpecification;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"fixed_asset"')]
class FixedAsset
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    /** МОЛ */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'financially_responsible_person_id', referencedColumnName: 'id', nullable: false)]
    private User $financiallyResponsiblePerson;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    private Category $category;

    #[ORM\ManyToOne(targetEntity: Counterparty::class)]
    #[ORM\JoinColumn(name: 'counterparty_id', referencedColumnName: 'id')]
    private ?Counterparty $counterparty;

    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?Warehouse $warehouse;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'string')]
    private string $serialNumber;

    #[ORM\Column(type: 'string')]
    private string $inventoryNumber;

    #[ORM\ManyToOne(targetEntity: Unit::class)]
    #[ORM\JoinColumn(name: 'unit_id', referencedColumnName: 'id', nullable: false)]
    private Unit $unit;

    /** закупочная цена */
    #[ORM\Column(type: 'amount')]
    private Amount $purchasePrice;

    #[ORM\ManyToOne(targetEntity: Vat::class)]
    #[ORM\JoinColumn(name: 'vat_id', referencedColumnName: 'id')]
    private ?Vat $vat;

    /** остаточная стоимость */
    #[ORM\Column(type: 'amount')]
    private Amount $residualValue;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected ?DateTimeImmutable $inUseAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected ?DateTimeImmutable $decommissionedAt;

    #[ORM\Column(type: 'store_fixed_asset_status')]
    private Status $status;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __construct(
        Uuid $id,
        Client $client,
        ?Employee $employee,
        Category $category,
        ?Counterparty $counterparty,
        ?Warehouse $warehouse,
        string $name,
        ?string $description,
        string $serialNumber,
        string $inventoryNumber,
        Unit $unit,
        Amount $purchasePrice,
        ?Vat $vat,
        ?Amount $residualValue,
        DateTimeImmutable $date,
        Status $status,
        FixedAssetSpecification $fixedAssetSpecification
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->financiallyResponsiblePerson = $employee !== null ? $employee->getUser() : $this->client->getUser();
        $this->category = $category;
        $this->counterparty = $counterparty;
        $this->warehouse = $warehouse;
        $this->name = $name;
        $this->description = $description;
        $this->serialNumber = $serialNumber;
        $this->inventoryNumber = $inventoryNumber;
        $this->unit = $unit;
        $this->purchasePrice = $purchasePrice;
        $this->vat = $vat;
        $this->residualValue = $residualValue ?? $purchasePrice;
        $this->status = $status;

        $this->createdAt = $date;

        $fixedAssetSpecification->check($this, $employee);
    }

    public function inUse(): void
    {
        if (!$this->status->isStorage()) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Invalid status transition from %s to %s for fixed asset %s',
                    $this->status->getName(),
                    'IN_USE',
                    $this->getId()->getValue()
                ),
            );
        }

        $this->status = Status::in_use();
        $this->inUseAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function storage(): void
    {
        if (!$this->status->isInUse()) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Invalid status transition from %s to %s for fixed asset %s',
                    $this->status->getName(),
                    'STORAGE',
                    $this->getId()->getValue()
                ),
            );
        }

        $this->status = Status::storage();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function decommissioned(): void
    {
        if (!$this->status->isInUse() && !$this->status->isStorage()) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Invalid status transition from %s to %s for fixed asset %s',
                    $this->status->getName(),
                    'DECOMMISSIONED',
                    $this->getId()->getValue()
                ),
            );
        }

        $this->status = Status::decommissioned();
        $this->decommissionedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function update(
        Category $category,
        ?Employee $employee,
        ?Counterparty $counterparty,
        ?Warehouse $warehouse,
        string $name,
        ?string $description,
        string $serialNumber,
        string $inventoryNumber,
        Unit $unit,
        Amount $purchasePrice,
        ?Vat $vat,
        FixedAssetSpecification $fixedAssetSpecification
    ): void {
        $this->category = $category;
        $this->financiallyResponsiblePerson = $employee !== null ? $employee->getUser() : $this->client->getUser();
        $this->counterparty = $counterparty;
        $this->warehouse = $warehouse;
        $this->name = $name;
        $this->description = $description;
        $this->serialNumber = $serialNumber;
        $this->inventoryNumber = $inventoryNumber;
        $this->unit = $unit;
        $this->purchasePrice = $purchasePrice;
        $this->vat = $vat;

        $this->updatedAt = new DateTimeImmutable();

        $fixedAssetSpecification->check($this, $employee);
    }

    public function getFinanciallyResponsiblePerson(): User
    {
        return $this->financiallyResponsiblePerson;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getCounterparty(): ?Counterparty
    {
        return $this->counterparty;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }

    public function getUnit(): Unit
    {
        return $this->unit;
    }

    public function getPurchasePrice(): Amount
    {
        return $this->purchasePrice;
    }

    public function getVat(): ?Vat
    {
        return $this->vat;
    }

    public function getResidualValue(): Amount
    {
        return $this->residualValue;
    }

    public function inUseAt(): ?DateTimeImmutable
    {
        return $this->inUseAt;
    }

    public function decommissionedAt(): ?DateTimeImmutable
    {
        return $this->decommissionedAt;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
