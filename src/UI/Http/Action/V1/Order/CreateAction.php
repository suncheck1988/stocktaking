<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Order;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Order\Command\Order\Create\Command;
use App\Order\Command\Order\Create\ItemDto;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/order',
        description: 'Создание заказа',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['responsibleUserId', 'paymentMethodId', 'deliveryTypeId', 'address', 'items'],
                properties: [
                    new OA\Property(property: 'responsibleUserId', type: 'string'),
                    new OA\Property(property: 'counterpartyId', type: 'string'),
                    new OA\Property(property: 'paymentMethodId', type: 'string'),
                    new OA\Property(property: 'deliveryTypeId', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'comment', type: 'string'),
                    new OA\Property(property: 'deliveryPrice', type: 'float'),
                    new OA\Property(property: 'items', type: 'array'),
                ],
            )
        ),
        tags: ['order'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное основного заказа',
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
            [Permission::SECTION_ORDERS],
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

        $items = [];
        foreach ($paramsExtractor->getArray('items') as $item) {
            $items[] = new ItemDto(
                $item->getString('id'),
                $item->getFloat('price'),
                $item->getInt('quantity'),
            );
        }

        return new Command(
            $paramsExtractor->getString('responsibleUserId'),
            $paramsExtractor->getStringOrNull('counterpartyId'),
            $paramsExtractor->getString('paymentMethodId'),
            $paramsExtractor->getString('deliveryTypeId'),
            $paramsExtractor->getString('address'),
            $paramsExtractor->getStringOrNull('comment'),
            $paramsExtractor->getFloatOrNull('deliveryPrice'),
            $items
        );
    }
}
