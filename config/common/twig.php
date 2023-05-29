<?php

declare(strict_types=1);

use App\Application\Service\Twig\FrontendUrlGenerator;
use App\Application\Service\Twig\FrontendUrlTwigExtension;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;

/**
 * @psalm-suppress PossiblyFalseArgument
 */
return [
    Twig::class => static function (): Twig {
        return Twig::create(
            __DIR__ . '/../../templates',
            [
                'cache' => __DIR__ . '/../../var/cache/twig',
                'debug' => getenv('APP_ENV') === 'test' || getenv('APP_ENV') === 'dev',
            ]
        );
    },

    Environment::class => static function (ContainerInterface $container): Environment {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *     extensions:string[],
         * } $config
         */
        $config = $container->get('config')['twig'];

        $loader = new FilesystemLoader(__DIR__ . '/../../templates');

        $environment = new Environment($loader, [
            __DIR__ . '/../../var/cache/twig',
        ]);

        foreach ($config['extensions'] as $class) {
            /** @var ExtensionInterface $extension */
            $extension = $container->get($class);
            $environment->addExtension($extension);
        }

        return $environment;
    },

    FrontendUrlGenerator::class => static fn (): FrontendUrlGenerator => new FrontendUrlGenerator(getenv('FRONTEND_URL')),

    'config' => [
        'twig' => [
            'extensions' => [
                FrontendUrlTwigExtension::class,
            ],
        ],
    ],
];
