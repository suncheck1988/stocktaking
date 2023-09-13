<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Counterparty;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Client\Command\Counterparty\Inactive\Command;
use App\UI\Http\Action\AbstractAction;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Patch(
        path: '/v1/counterparty/{id}/inactive',
        description: 'Деактивация контрагента',
        security: [['bearerAuth' => '[]']],
        tags: ['counterparty'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешная деактивация контрагента',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class InactiveAction extends AbstractAction
{
    /**
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_COUNTERPARTIES],
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
