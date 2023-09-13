<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\DeliveryType;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Order\Repository\DeliveryTypeRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\DeliveryType\Dto\DeliveryType\DeliveryTypeResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/delivery-type/{id}',
        description: 'Получение типа доставки',
        security: [['bearerAuth' => '[]']],
        tags: ['delivery-type'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о типе доставки',
                content: new OA\JsonContent(ref: DeliveryTypeResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly DeliveryTypeRepository $deliveryTypeRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_DELIVERY_TYPES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $deliveryType =  $this->deliveryTypeRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(DeliveryTypeResponse::fromModel($deliveryType)->jsonSerialize());
    }
}
