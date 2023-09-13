<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Position;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Command\Position\Create\BalanceDto;
use App\Store\Command\Position\Create\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/position',
        description: 'Создание позиции',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['categoryId', 'name', 'price', 'unitId', 'balance'],
                properties: [
                    new OA\Property(property: 'categoryId', type: 'string'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'float'),
                    new OA\Property(property: 'vatId', type: 'string', nullable: true),
                    new OA\Property(property: 'unitId', type: 'string'),
                    new OA\Property(property: 'balance', type: 'array'),
                ],
            )
        ),
        tags: ['position'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное создание позиции',
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
            [Permission::SECTION_POSITIONS],
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

        $balance = [];
        foreach ($paramsExtractor->getArray('balance') as $item) {
            $balance[] = new BalanceDto(
                $item->getString('warehouseId'),
                $item->getInt('quantity')
            );
        }

        return new Command(
            $paramsExtractor->getString('categoryId'),
            trim($paramsExtractor->getString('name')),
            $paramsExtractor->getStringOrNull('description'),
            $paramsExtractor->getFloat('price'),
            $paramsExtractor->getStringOrNull('vatId'),
            $paramsExtractor->getString('unitId'),
            $balance
        );
    }
}
