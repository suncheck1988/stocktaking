<?php

namespace App\Application\Repository;

use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;

interface ClientableInterface
{
    public function get(Uuid $id, Client $client): object;
}