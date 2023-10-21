<?php

declare(strict_types=1);

use App\Application\ErrorHandler\SentryDecorator;
use App\Application\Service\Sentry\Sentry;
use App\UI\Http\ErrorHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Middleware\ErrorMiddleware;
use Symfony\Contracts\Translation\TranslatorInterface;

return [
    ErrorMiddleware::class => static function (ContainerInterface $container): ErrorMiddleware {
        $translator = $container->get(TranslatorInterface::class);
        $callableResolver = $container->get(CallableResolverInterface::class);
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{display_details:bool} $config
         */
        $config = $container->get('config')['errors'];

        $middleware = new ErrorMiddleware(
            $callableResolver,
            $responseFactory,
            $config['display_details'],
            true,
            true
        );

        $logger = $container->get(LoggerInterface::class);

        $errorHandler = new ErrorHandler($translator, $callableResolver, $responseFactory, $logger);

        $middleware->setDefaultErrorHandler(
            new SentryDecorator(
                $errorHandler,
                $container->get(Sentry::class)
            )
        );

        return $middleware;
    },

    'config' => [
        'errors' => [
            'display_details' => (bool)getenv('APP_DEBUG'),
        ],
    ],
];
