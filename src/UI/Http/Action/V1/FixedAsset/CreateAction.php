<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\FixedAsset;

use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Command\FixedAsset\Create\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/fixed-asset',
        description: 'Создание основного средства',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['categoryId', 'name', 'serialNumber', 'inventoryNumber', 'unitId', 'purchasePrice', 'status'],
                properties: [
                    new OA\Property(property: 'financiallyResponsiblePersonId', type: 'string', nullable: true),
                    new OA\Property(property: 'categoryId', type: 'string'),
                    new OA\Property(property: 'counterpartyId', type: 'string', nullable: true),
                    new OA\Property(property: 'warehouseId', type: 'string', nullable: true),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'serialNumber', type: 'string'),
                    new OA\Property(property: 'inventoryNumber', type: 'string'),
                    new OA\Property(property: 'unitId', type: 'string'),
                    new OA\Property(property: 'purchasePrice', type: 'float'),
                    new OA\Property(property: 'vatId', type: 'string', nullable: true),
                    new OA\Property(property: 'status', type: 'int'),
                ],
            )
        ),
        tags: ['fixed-asset'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное создание основного средства',
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
            $paramsExtractor->getStringOrNull('vatId'),
            $paramsExtractor->getInt('status')
        );
    }
}
