<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

if (file_exists(__DIR__ . '/../.env')) {
    (new Dotenv('APP_ENV'))->usePutenv()->load(__DIR__ . '/../.env');
}

$cli = new Application('Console');

try {
    /**
     * @var string[] $commands
     * @psalm-suppress MixedArrayAccess
     */
    $commands = $container->get('config')['console']['commands'];
} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
    return $e->getMessage();
}

try {
    $entityManager = $container->get(EntityManagerInterface::class);

    /**
     * @psalm-suppress DeprecatedClass
     */
    $cli->getHelperSet()->set(new EntityManagerHelper($entityManager), 'em');
} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
    return $e->getMessage();
}

foreach ($commands as $name) {
    try {
        /** @var Command $command */
        $command = $container->get($name);
        $cli->add($command);
    } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        return $e->getMessage();
    }
}

try {
    $cli->run();
} catch (Exception $e) {
    return $e->getMessage();
}
