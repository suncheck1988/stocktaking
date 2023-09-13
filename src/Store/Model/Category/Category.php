<?php

declare(strict_types=1);

namespace App\Store\Model\Category;

use App\Application\Model\ClientableTrait;
use App\Application\Model\IdentifiableTrait;
use App\Application\Model\TimestampableTrait;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;
use App\Store\Exception\Category\CategoryAlreadyExistException;
use App\Store\Specification\Category\UniqueCategoryNameSpecification;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

#[ORM\Entity]
#[ORM\Table(name: '"category"')]
class Category
{
    use ClientableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    private ?Category $parent;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['all'])]
    private Collection $children;

    #[ORM\Column(type: 'store_category_status')]
    private Status $status;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __construct(
        Uuid $id,
        Client $client,
        ?self $parent,
        string $name,
        DateTimeImmutable $date,
        UniqueCategoryNameSpecification $uniqueCategoryNameSpecification
    ) {
        $this->id = $id;
        $this->client = $client;
        $this->parent = $parent;
        $this->name = $name;
        $this->status = Status::active();

        $this->children = new ArrayCollection();

        $this->createdAt = $date;

        if ($uniqueCategoryNameSpecification->isSatisfiedBy($this) === false) {
            throw new CategoryAlreadyExistException($this->name);
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

    public function changeParent(?self $parent): void
    {
        $this->parent = $parent;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function addChild(
        Uuid $uuid,
        string $name,
        DateTimeImmutable $date,
        UniqueCategoryNameSpecification $uniqueCategoryNameSpecification
    ): void {
        $child = new self($uuid, $this->client, $this, $name, $date, $uniqueCategoryNameSpecification);

        $this->children->add($child);
    }

    /**
     * @return Category[]
     */
    public function getChildren(): array
    {
        /** @var Category[] $result */
        $result = $this->children->toArray();

        return $result;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
