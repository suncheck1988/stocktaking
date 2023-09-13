<?php

declare(strict_types=1);

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @psalm-suppress ArgumentTypeCoercion
 * @psalm-suppress InvalidArgument
 * @psalm-suppress MixedArrayOffset
 */
return [
    EntityManagerInterface::class => static function (ContainerInterface $container): EntityManagerInterface {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *     metadata_dirs:string[],
         *     dev_mode:bool,
         *     proxy_dir:string,
         *     cache_dir:string,
         *     types:array<string,class-string<Doctrine\DBAL\Types\Type>>,
         *     subscribers:string[],
         *     connection:array<string, mixed>,
         * } $settings
         */
        $settings = $container->get('config')['doctrine'];

        $config = ORMSetup::createConfiguration(
            $settings['dev_mode'],
            $settings['proxy_dir'],
            $settings['cache_dir'] ?
                new FilesystemAdapter('', 0, $settings['cache_dir']) :
                new ArrayAdapter()
        );

        $config->setMetadataDriverImpl(new AttributeDriver($settings['metadata_dirs']));

        $config->setNamingStrategy(new UnderscoreNamingStrategy());

        /**
         * @psalm-suppress MixedArgument
         */
        foreach ($settings['types'] as $class) {
            if (!DBAL\Types\Type::hasType($class::NAME)) {
                DBAL\Types\Type::addType($class::NAME, $class);
            }
        }

        $eventManager = new EventManager();

        foreach ($settings['subscribers'] as $name) {
            /** @var EventSubscriber $subscriber */
            $subscriber = $container->get($name);
            $eventManager->addEventSubscriber($subscriber);
        }

        $connection = DriverManager::getConnection($settings['connection']);

        return new EntityManager($connection, $config, $eventManager);
    },
    Connection::class => static function (ContainerInterface $container): Connection {
        $em = $container->get(EntityManagerInterface::class);
        return $em->getConnection();
    },
    'config' => [
        'doctrine' => [
            'dev_mode' => false,
            'cache_dir' => __DIR__ . '/../../var/cache/doctrine/cache',
            'proxy_dir' => __DIR__ . '/../../var/cache/doctrine/proxy',
            'connection' => [
                'driver' => 'pdo_pgsql',
                'host' => getenv('POSTGRES_HOST'),
                'user' => getenv('POSTGRES_USER'),
                'password' => getenv('POSTGRES_PASSWORD'),
                'dbname' => getenv('POSTGRES_DATABASE'),
                'charset' => 'utf-8',
            ],
            'subscribers' => [],
            'metadata_dirs' => [
                __DIR__ . '/../../src/Auth/Model',
                __DIR__ . '/../../src/Client/Model',
                __DIR__ . '/../../src/Order/Model',
                __DIR__ . '/../../src/Store/Model',
            ],
            'types' => [
                App\Data\Doctrine\Type\AmountType::class,
                App\Data\Doctrine\Type\PhoneType::class,
                App\Data\Doctrine\Type\UuidType::class,

                App\Data\Doctrine\Type\Auth\UserEmailType::class,
                App\Data\Doctrine\Type\Auth\UserPermissionType::class,
                App\Data\Doctrine\Type\Auth\UserRoleType::class,
                App\Data\Doctrine\Type\Auth\UserStatusType::class,

                App\Data\Doctrine\Type\Auth\UserEmailConfirm\UserEmailConfirmStatusType::class,
                App\Data\Doctrine\Type\Auth\UserEmailConfirm\UserEmailConfirmTypeType::class,

                App\Data\Doctrine\Type\Client\Counterparty\CounterpartyStatusType::class,

                App\Data\Doctrine\Type\Order\DeliveryType\DeliveryTypeStatusType::class,
                App\Data\Doctrine\Type\Order\OrderItem\OrderItemStatusType::class,
                App\Data\Doctrine\Type\Order\OrderStatusType::class,
                App\Data\Doctrine\Type\Order\PaymentMethod\PaymentMethodStatusType::class,

                App\Data\Doctrine\Type\Store\Category\CategoryStatusType::class,
                App\Data\Doctrine\Type\Store\FixedAsset\FixedAssetStatusType::class,
                App\Data\Doctrine\Type\Store\Position\PositionBalance\PositionBalanceStatusType::class,
                App\Data\Doctrine\Type\Store\Position\PositionStatusType::class,
                App\Data\Doctrine\Type\Store\Unit\UnitStatusType::class,
                App\Data\Doctrine\Type\Store\Vat\VatStatusType::class,
                App\Data\Doctrine\Type\Store\Warehouse\WarehouseStatusType::class,
            ],
        ],
    ],
];
