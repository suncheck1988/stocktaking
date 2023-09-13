<?php

declare(strict_types=1);

namespace App\Application\Repository;

use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;

interface ClientableRepositoryInterface
{
    public function get(Uuid $id, Client $client): object;
    public function fetchAll(Client $client): array;
    public function count(Client $client): int;
}
