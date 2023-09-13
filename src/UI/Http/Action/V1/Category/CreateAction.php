<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Category;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Command\Category\Create\ChildDto;
use App\Store\Command\Category\Create\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/category',
        description: 'Создание корневой категории',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['name', 'children'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'children', type: 'array'),
                ],
            )
        ),
        tags: ['category'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное создание категории',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class CreateAction extends AbstractAction
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

        $children = [];
        foreach ($paramsExtractor->getArray('children') as $child) {
            $children[] = new ChildDto($child->getString('name'));
        }

        return new Command(
            trim($paramsExtractor->getString('name')),
            $children
        );
    }
}
