<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\FixedAsset;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Command\FixedAsset\Update\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Patch(
        path: '/v1/fixed-asset/{id}',
        description: 'Обновление основного средства',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['categoryId', 'name', 'serialNumber', 'inventoryNumber', 'unitId', 'purchasePrice'],
                properties: [
                    new OA\Property(property: 'financiallyResponsiblePersonId', type: 'string'),
                    new OA\Property(property: 'categoryId', type: 'string'),
                    new OA\Property(property: 'counterpartyId', type: 'string'),
                    new OA\Property(property: 'warehouseId', type: 'string'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'serialNumber', type: 'string'),
                    new OA\Property(property: 'inventoryNumber', type: 'string'),
                    new OA\Property(property: 'unitId', type: 'string'),
                    new OA\Property(property: 'purchasePrice', type: 'float'),
                    new OA\Property(property: 'vatId', type: 'string'),
                ],
            )
        ),
        tags: ['fixed-asset'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное обновление основного средства',
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
            [Permission::SECTION_FIXED_ASSETS],
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
            $this->resolveArg('id'),
            $paramsExtractor->getStringOrNull('financiallyResponsiblePersonId'),
            $paramsExtractor->getString('categoryId'),
            $paramsExtractor->getStringOrNull('counterpartyId'),
            $paramsExtractor->getStringOrNull('warehouseId'),
            trim($paramsExtractor->getString('name')),
            $paramsExtractor->getStringOrNull('description'),
            $paramsExtractor->getString('serialNumber'),
            $paramsExtractor->getString('inventoryNumber'),
            $paramsExtractor->getString('unitId'),
            $paramsExtractor->getFloat('purchasePrice'),
            $paramsExtractor->getStringOrNull('vatId')
        );
    }
}
