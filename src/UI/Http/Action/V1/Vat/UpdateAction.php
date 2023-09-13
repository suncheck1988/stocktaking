<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Vat;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Command\Vat\Update\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Patch(
        path: '/v1/vat/{id}',
        description: 'Обновление ставки НДС',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['value', 'isDefault'],
                properties: [
                    new OA\Property(property: 'value', type: 'int'),
                    new OA\Property(property: 'isDefault', type: 'boolean'),
                ],
            )
        ),
        tags: ['vat'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное обновление ставки НДС',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class UpdateAction extends AbstractAction
{
    /**
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_VATS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $command = $this->deserialize($request);
        $this->validator->validate($command);

        $this->bus->handle($command);

        return $this->asEmpty();
    }

    private function deserialize(ServerRequestInterface $request): Command
    {
        $paramsExtractor = ParamsExtractor::fromRequest($request);

        return new Command(
            $this->resolveArg('id'),
            $paramsExtractor->getInt('value'),
            $paramsExtractor->getBool('isDefault')
        );
    }
}
