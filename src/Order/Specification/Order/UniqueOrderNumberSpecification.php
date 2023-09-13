<?php

declare(strict_types=1);

namespace App\Order\Specification\Order;

use App\Client\Model\Client\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class UniqueOrderNumberSpecification
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws Exception
     */
    public function generate(Client $client): int
    {
        $number = (int)$this->connection->createQueryBuilder()
            ->select('MAX(number)')
            ->from('"order"')
            ->where('order.client = :client')
            ->setParameter('client', $client)
            ->fetchOne();

        return $number + 1;
    }
}
