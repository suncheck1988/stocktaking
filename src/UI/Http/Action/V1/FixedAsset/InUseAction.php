<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\FixedAsset;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Command\FixedAsset\InUse\Command;
use App\UI\Http\Action\AbstractAction;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Patch(
        path: '/v1/fixed-asset/{id}/in-use',
        description: 'Выдача основного средства в использование',
        security: [['bearerAuth' => '[]']],
        tags: ['fixed-asset'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешная выдача основного средства в использование',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class InUseAction extends AbstractAction
{
    /**
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_FIXED_ASSETS],
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
