<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Client\Counterparty;

use App\Client\Model\Counterparty\Status;
use App\Data\Doctrine\Type\EnumType;

class CounterpartyStatusType extends EnumType
{
    public const NAME = 'client_counterparty_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
