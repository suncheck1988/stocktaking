<?php

declare(strict_types=1);

namespace App\Application\Service\CommandBus;

class ClassNameExtractor extends \League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor
{
    public function extract($command): string
    {
        $commandClassName = parent::extract($command);
        return substr($commandClassName, 0, \strlen($commandClassName) - 7) . 'Handler';
    }
}
