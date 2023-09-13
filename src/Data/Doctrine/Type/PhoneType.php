<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type;

use App\Application\ValueObject\Phone;

class PhoneType extends StringType
{
    public const NAME = 'phone';

    protected function getClassName(): string
    {
        return Phone::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
