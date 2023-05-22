<?php

declare(strict_types=1);

namespace App\Application\Interface;

use App\Client\Model\Client\Client;

interface ClientAwareInterface
{
    public function getClient(): Client;
    public function setClient(): void;
}
