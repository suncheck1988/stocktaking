<?php

declare(strict_types=1);

namespace App\Application\Interface;

use App\Application\Exception\DomainException;
use App\Client\Model\Client\Client;

trait ClientAwareTrait
{
    protected ?Client $client = null;

    public function getClient(): Client
    {
        $client = $this->client;

        if ($client === null) {
            throw new DomainException('Клиент не найден');
        }

        if (!$client->getUser()->getStatus()->isActive()) {
            throw new DomainException('Клиент не активен');
        }

        return $client;
    }

    public function setClient(): void
    {
        $currentUser = $this->getCurrentUser();
        if ($currentUser !== null) {
            $this->client = $this->clientFinder->findByUser($currentUser);
        }
    }
}
