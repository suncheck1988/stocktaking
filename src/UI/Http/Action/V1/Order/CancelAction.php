<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Order;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Order\Command\Order\Cancel\Command;
use App\UI\Http\Action\AbstractAction;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/order/{id}/cancel',
        description: 'Отмена заказа',
        tags: ['order'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешная отмена заказа',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class CancelAction extends AbstractAction
{
    /**
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_ORDERS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $command = $this->deserialize();
        $this->validator->validate($command);

        $this->bus->handle($command);

        return $this->asEmpty();
    }

    private function deserialize(): Command
    {
        return new Command($this->resolveArg('id'));
    }
}
