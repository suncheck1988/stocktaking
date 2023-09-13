<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Category;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Repository\CategoryRepository;
use App\UI\Http\Action\AbstractAction;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/category/tree',
        description: 'Получение списка категорий в виде дерева',
        security: [['bearerAuth' => '[]']],
        tags: ['category'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список категорий клиента в виде дерева',
            ),
        ]
    )
]
class TreeAction extends AbstractAction
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    /**
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_CATEGORIES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $result = [];

        $items = $this->categoryRepository->fetchAll($this->authContext->getClient());
        foreach ($items as $item) {
            $parent = $item->getParent();
            if ($parent === null) {
                if (!isset($result[$item->getId()->getValue()])) {
                    $result[$item->getId()->getValue()] = [
                        'id' => $item->getId()->getValue(),
                        'name' => $item->getName(),
                        'children' => [],
                    ];
                } else {
                    $result[$item->getId()->getValue()]['id'] = $item->getId()->getValue();
                    $result[$item->getId()->getValue()]['name'] = $item->getName();
                }
            } else {
                $result[$parent->getId()->getValue()]['children'][$item->getId()->getValue()] = [
                    'id' => $item->getId()->getValue(),
                    'name' => $item->getName(),
                ];
            }
        }

        return $this->asJson(
            [
                'data' => $result,
            ]
        );
    }
}
