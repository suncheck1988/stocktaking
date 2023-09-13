<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Position;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Command\Position\Update\BalanceDto;
use App\Store\Command\Position\Update\Command;
use App\Store\Command\Position\Update\NewBalanceDto;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Patch(
        path: '/v1/position/{id}',
        description: 'Обновление позиции',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['categoryId', 'name', 'price', 'unitId'],
                properties: [
                    new OA\Property(property: 'categoryId', type: 'string'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'price', type: 'float'),
                    new OA\Property(property: 'vatId', type: 'string'),
                    new OA\Property(property: 'unitId', type: 'string'),
                    new OA\Property(property: 'existsBalance', type: 'array'),
                    new OA\Property(property: 'newBalance', type: 'array'),
                ],
            )
        ),
        tags: ['position'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное обновление позиции',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class UpdateAction extends AbstractAction
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
        foreach ($paramsExtractor->getArray('existsBalance') as $item) {
            $balance[] = new BalanceDto(
                $item->getString('warehouseId'),
                $item->getInt('quantity'),
                $item->getBool('isRemove')
            );
        }

        $newBalance = [];
        foreach ($paramsExtractor->getArray('newBalance') as $item) {
            $newBalance[] = new NewBalanceDto(
                $item->getString('warehouseId'),
                $item->getInt('quantity')
            );
        }

        return new Command(
            $this->resolveArg('id'),
            $paramsExtractor->getString('categoryId'),
            trim($paramsExtractor->getString('name')),
            $paramsExtractor->getStringOrNull('description'),
            $paramsExtractor->getFloat('price'),
            $paramsExtractor->getStringOrNull('vatId'),
            $paramsExtractor->getString('unitId'),
            $balance,
            $newBalance
        );
    }
}
