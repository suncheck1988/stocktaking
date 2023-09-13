<?php

declare(strict_types=1);

namespace Test\Functional;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Test\TestKernel;

abstract class WebTestCase extends TestCase
{
    protected Client $client;

    protected Container $container;

    protected Authenticator $authenticator;

    private EntityManagerInterface $entityManager;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->container = TestKernel::getContainer();
        $this->entityManager = $this->container->get(EntityManagerInterface::class);
        $this->authenticator = $this->container->get(Authenticator::class);

        parent::__construct();
    }

    /**
     * @param array<int, FixtureInterface> $fixtures
     */
    protected function loadFixtures(array $fixtures): void
    {
        $loader = new Loader();
        foreach ($fixtures as $fixture) {
            $loader->addFixture($fixture);
        }

        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    protected static function assertUuid(string $string): void
    {
        self::assertMatchesRegularExpression('~^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$~i', $string);
    }
}