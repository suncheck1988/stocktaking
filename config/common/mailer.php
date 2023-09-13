<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\EventListener\EnvelopeListener;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;

/**
 * @psalm-suppress PossiblyFalseArgument
 */
return [
    MailerInterface::class => static function (ContainerInterface $container): MailerInterface {
        $dispatcher = new EventDispatcher();

        $dispatcher->addSubscriber(new EnvelopeListener(new Address(
            getenv('MAILER_FROM_EMAIL'),
            'Stocktaking'
        )));

        $transport = (new EsmtpTransport(
            getenv('MAILER_HOST'),
            (int)getenv('MAILER_PORT'),
            false,
            $dispatcher,
            $container->get(LoggerInterface::class)
        ))
            ->setUsername(getenv('MAILER_USERNAME'))
            ->setPassword(getenv('MAILER_PASSWORD'));

        return new Mailer($transport);
    },
];
