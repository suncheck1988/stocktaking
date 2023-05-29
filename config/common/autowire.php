<?php

declare(strict_types=1);

use App\Application\Service\Validator\Validator;
use App\Auth\Service\AuthContext;
use DI\Definition\Helper\CreateDefinitionHelper;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

use function DI\autowire;
use function DI\get;

$actionInit = static fn (): CreateDefinitionHelper => autowire()->method(
    'init',
    get(Validator::class),
    get(CommandBus::class),
    get(AuthContext::class),
    get(LoggerInterface::class),
);

return [
    'App\UI\Http\Action\*\*Action' => $actionInit(),
    'App\UI\Http\Action\*\*\*Action' => $actionInit(),
    'App\UI\Http\Action\*\*\*\*Action' => $actionInit(),
    'App\UI\Http\Action\*\*\*\*\*Action' => $actionInit(),
];
