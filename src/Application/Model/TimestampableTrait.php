<?php

declare(strict_types=1);

namespace App\Application\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;

trait TimestampableTrait
{
    #[ORM\Column(type: 'datetime_immutable')]
    protected DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected ?DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function triggerCreatedAt(): void
    {
        try {
            $this->createdAt = new DateTimeImmutable();
        } catch (Exception) {
        }
    }

    public function triggerUpdatedAt(): void
    {
        try {
            $this->updatedAt = new DateTimeImmutable();
        } catch (Exception) {
        }
    }
}
