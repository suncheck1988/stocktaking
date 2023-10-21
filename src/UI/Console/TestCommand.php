<?php

declare(strict_types=1);

namespace App\UI\Console;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('test');
    }

    /**
     * @psalm-suppress ForbiddenCode
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->error('test', [
            'from' => 'logger',
            'redis_host' => (string)getenv('REDIS_HOST'),
            'host' => getenv('POSTGRES_HOST'),
            'user' => getenv('POSTGRES_USER'),
            'password' => getenv('POSTGRES_PASSWORD'),
            'dbname' => getenv('POSTGRES_DATABASE'),
            'APP_ENV' => getenv('APP_ENV'),
            'MAILER_HOST' => getenv('MAILER_HOST'),
            'MAILER_PORT' => getenv('MAILER_PORT'),
            'MAILER_USERNAME' => getenv('MAILER_USERNAME'),
            'MAILER_PASSWORD' => getenv('MAILER_PASSWORD'),
            'MAILER_FROM_EMAIL' => getenv('MAILER_FROM_EMAIL'),
            'SENTRY_DSN' => getenv('SENTRY_DSN'),
        ]);

        return self::SUCCESS;
    }
}
