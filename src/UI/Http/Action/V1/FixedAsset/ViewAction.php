<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\FixedAsset;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Repository\FixedAssetRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\FixedAsset\Dto\FixedAsset\FixedAssetResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/fixed-asset/{id}',
        description: 'Получение основного средства',
        security: [['bearerAuth' => '[]']],
        tags: ['fixed-asset'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о основном средстве',
                content: new OA\JsonContent(ref: FixedAssetResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly FixedAssetRepository $fixedAssetRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_FIXED_ASSETS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $fixedAsset =  $this->fixedAssetRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(FixedAssetResponse::fromModel($fixedAsset)->jsonSerialize());
    }
}
