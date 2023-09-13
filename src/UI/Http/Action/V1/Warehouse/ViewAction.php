<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Warehouse;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Repository\WarehouseRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Warehouse\Dto\Warehouse\WarehouseResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/warehouse/{id}',
        description: 'Получение склада',
        security: [['bearerAuth' => '[]']],
        tags: ['warehouse'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о складе',
                content: new OA\JsonContent(ref: WarehouseResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly WarehouseRepository $warehouseRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_WAREHOUSES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $warehouse =  $this->warehouseRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(WarehouseResponse::fromModel($warehouse)->jsonSerialize());
    }
}
