<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\DeliveryType;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Order\Command\DeliveryType\Create\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/delivery-type',
        description: 'Создание типа доставки',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                ],
            )
        ),
        tags: ['delivery-type'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное создание типа доставки',
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
            [Permission::SECTION_DELIVERY_TYPES],
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
            trim($paramsExtractor->getString('name'))
        );
    }
}
