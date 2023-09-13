<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Vat;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Repository\VatRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Vat\Dto\Vat\VatResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/vat/{id}',
        description: 'Получение ставки НДС',
        security: [['bearerAuth' => '[]']],
        tags: ['vat'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о ставке НДС',
                content: new OA\JsonContent(ref: VatResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly VatRepository $vatRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_VATS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $vat =  $this->vatRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(VatResponse::fromModel($vat)->jsonSerialize());
    }
}
