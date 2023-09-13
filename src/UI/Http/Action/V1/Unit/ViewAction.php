<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Unit;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Repository\UnitRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Unit\Dto\Unit\UnitResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/unit/{id}',
        description: 'Получение единицы измерения',
        security: [['bearerAuth' => '[]']],
        tags: ['unit'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о единице измерения',
                content: new OA\JsonContent(ref: UnitResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly UnitRepository $unitRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_POSITION_UNITS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $unit =  $this->unitRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(UnitResponse::fromModel($unit)->jsonSerialize());
    }
}
