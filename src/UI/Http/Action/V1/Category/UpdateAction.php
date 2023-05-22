<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Category;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Command\Category\Update\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Patch(
        path: '/v1/category/{id}',
        description: 'Обновление категории',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'parentId', type: 'string'),
                ],
            )
        ),
        tags: ['category'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное обновление категории',
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
            [Permission::SECTION_CATEGORIES],
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
            $paramsExtractor->getString('name'),
            $paramsExtractor->getString('parentId'),
        );
    }
}
