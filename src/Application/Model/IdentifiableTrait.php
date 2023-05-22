<?php

declare(strict_types=1);

namespace App\Application\Model;

use App\Application\ValueObject\Uuid;
use Doctrine\ORM\Mapping as ORM;

trait IdentifiableTrait
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    protected Uuid $id;

    public function getId(): Uuid
    {
        return $this->id;
    }
}
