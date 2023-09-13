<?php

declare(strict_types=1);

use App\UI\Http\Action;
use App\UI\Http\Action\SwaggerAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return static function (App $app): void {
    $app->get('/', Action\HomeAction::class);
    $app->get('/doc', SwaggerAction::class);

    $app->group('/v1/auth', function (Group $group): void {
        $group->group('/registration', function (Group $group1): void {
            $group1->group('/client', function (Group $group2): void {
                $group2->post('/request', Action\V1\Auth\Registration\Client\RequestAction::class);
            });
            $group1->group('/employee', function (Group $group2): void {
                $group2->post('/request', Action\V1\Auth\Registration\Employee\RequestAction::class);
            });

            $group1->post('/confirm', Action\V1\Auth\Registration\ConfirmAction::class);
            $group1->post('/recreate-email-confirm', Action\V1\Auth\Registration\RecreateEmailConfirmAction::class);
        });

        $group->group('/login', function (Group $group1): void {
            $group1->post('/request', Action\V1\Auth\Login\RequestAction::class);
        });
    });

    $app->group('/v1/user', function (Group $group): void {
        $group->group('/reset-password', function (Group $group2): void {
            $group2->post('/request', Action\V1\User\ResetPassword\RequestAction::class);
            $group2->post('/confirm', Action\V1\User\ResetPassword\ConfirmAction::class);
        });

        $group->get('/me', Action\V1\User\MeAction::class);
    });

    $app->group('/v1/employee', function (Group $group): void {
        $group->get('', Action\V1\Employee\IndexAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\Employee\ViewAction::class);
            $group1->patch('', Action\V1\Employee\UpdateAction::class);
            /** @todo пока не используется */
            $group1->post('/change-permission', Action\V1\Employee\ChangePermissionAction::class);
            $group1->post('/block', Action\V1\Employee\BlockAction::class);
            $group1->post('/active', Action\V1\Employee\ActiveAction::class);
        });
    });

    $app->group('/v1/counterparty', function (Group $group): void {
        $group->get('', Action\V1\Counterparty\IndexAction::class);
        $group->post('', Action\V1\Counterparty\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\Counterparty\ViewAction::class);
            $group1->patch('', Action\V1\Counterparty\UpdateAction::class);
            $group1->post('/active', Action\V1\Counterparty\ActiveAction::class);
            $group1->post('/inactive', Action\V1\Counterparty\InactiveAction::class);
        });
    });

    $app->group('/v1/category', function (Group $group): void {
        $group->get('', Action\V1\Category\IndexAction::class);
        $group->get('/tree', Action\V1\Category\TreeAction::class);
        $group->post('', Action\V1\Category\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\Category\ViewAction::class);
            $group1->patch('', Action\V1\Category\UpdateAction::class);
            $group1->post('/active', Action\V1\Category\ActiveAction::class);
            $group1->post('/inactive', Action\V1\Category\InactiveAction::class);
        });
    });

    $app->group('/v1/warehouse', function (Group $group): void {
        $group->get('', Action\V1\Warehouse\IndexAction::class);
        $group->post('', Action\V1\Warehouse\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\Warehouse\ViewAction::class);
            $group1->patch('', Action\V1\Warehouse\UpdateAction::class);
            $group1->post('/active', Action\V1\Warehouse\ActiveAction::class);
            $group1->post('/inactive', Action\V1\Warehouse\InactiveAction::class);
        });
    });

    $app->group('/v1/position', function (Group $group): void {
        $group->get('', Action\V1\Position\IndexAction::class);
        $group->post('', Action\V1\Position\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\Position\ViewAction::class);
            $group1->patch('', Action\V1\Position\UpdateAction::class);
            $group1->post('/active', Action\V1\Position\ActiveAction::class);
            $group1->post('/inactive', Action\V1\Position\InactiveAction::class);
        });
    });

    $app->group('/v1/fixed-asset', function (Group $group): void {
        $group->get('', Action\V1\FixedAsset\IndexAction::class);
        $group->post('', Action\V1\FixedAsset\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\FixedAsset\ViewAction::class);
            $group1->patch('', Action\V1\FixedAsset\UpdateAction::class);
            $group1->post('/in-use', Action\V1\FixedAsset\InUseAction::class);
            $group1->post('/storage', Action\V1\FixedAsset\StorageAction::class);
            $group1->post('/decommissioned', Action\V1\FixedAsset\DecommissionedAction::class);
        });
    });

    $app->group('/v1/unit', function (Group $group): void {
        $group->get('', Action\V1\Unit\IndexAction::class);
        $group->post('', Action\V1\Unit\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\Unit\ViewAction::class);
            $group1->patch('', Action\V1\Unit\UpdateAction::class);
            $group1->post('/active', Action\V1\Unit\ActiveAction::class);
            $group1->post('/inactive', Action\V1\Unit\InactiveAction::class);
        });
    });

    $app->group('/v1/vat', function (Group $group): void {
        $group->get('', Action\V1\Vat\IndexAction::class);
        $group->post('', Action\V1\Vat\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\Vat\ViewAction::class);
            $group1->patch('', Action\V1\Vat\UpdateAction::class);
            $group1->post('/active', Action\V1\Vat\ActiveAction::class);
            $group1->post('/inactive', Action\V1\Vat\InactiveAction::class);
        });
    });

    $app->group('/v1/payment-method', function (Group $group): void {
        $group->get('', Action\V1\PaymentMethod\IndexAction::class);
        $group->post('', Action\V1\PaymentMethod\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\PaymentMethod\ViewAction::class);
            $group1->patch('', Action\V1\PaymentMethod\UpdateAction::class);
            $group1->post('/active', Action\V1\PaymentMethod\ActiveAction::class);
            $group1->post('/inactive', Action\V1\PaymentMethod\InactiveAction::class);
        });
    });

    $app->group('/v1/delivery-type', function (Group $group): void {
        $group->get('', Action\V1\DeliveryType\IndexAction::class);
        $group->post('', Action\V1\DeliveryType\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->get('', Action\V1\DeliveryType\ViewAction::class);
            $group1->patch('', Action\V1\DeliveryType\UpdateAction::class);
            $group1->post('/active', Action\V1\DeliveryType\ActiveAction::class);
            $group1->post('/inactive', Action\V1\DeliveryType\InactiveAction::class);
        });
    });

    $app->group('/v1/order', function (Group $group): void {
        $group->post('', Action\V1\Order\CreateAction::class);
        $group->group('/{id}', function (Group $group1): void {
            $group1->post('/cancel', Action\V1\Order\CancelAction::class);
        });
    });
};
