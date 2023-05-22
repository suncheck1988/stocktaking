<?php

declare(strict_types=1);

namespace App\Client\Repository;

use App\Application\Exception\NotFoundException;
use App\Application\Repository\AbstractRepository;
use App\Application\ValueObject\Uuid;
use App\Client\Model\Client\Client;

final class ClientRepository extends AbstractRepository
{
    public function add(Client $client): void
    {
        $this->entityManager->persist($client);
    }

    public function get(Uuid $id): Client
    {
        /** @var Client|null $model */
        $model = $this->entityRepository->find($id);
        if ($model === null) {
            throw new NotFoundException(sprintf('Client with id %s not found', $id->getValue()));
        }

        return $model;
    }

    protected function getModelClassName(): string
    {
        return Client::class;
    }
}
