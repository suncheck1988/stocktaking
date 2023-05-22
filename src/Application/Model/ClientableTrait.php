<?php

declare(strict_types=1);

namespace App\Application\Model;

use App\Client\Model\Client\Client;
use Doctrine\ORM\Mapping as ORM;

trait ClientableTrait
{
    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: false)]
    private Client $client;

    public function getClient(): Client
    {
        return $this->client;
    }
}
