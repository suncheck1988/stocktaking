<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\PaymentMethod;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Order\Repository\PaymentMethodRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\PaymentMethod\Dto\PaymentMethod\PaymentMethodResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/payment-method/{id}',
        description: 'Получение метода оплаты',
        security: [['bearerAuth' => '[]']],
        tags: ['payment-method'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о методе оплаты',
                content: new OA\JsonContent(ref: PaymentMethodResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly PaymentMethodRepository $paymentMethodRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_PAYMENT_METHODS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $paymentMethod =  $this->paymentMethodRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(PaymentMethodResponse::fromModel($paymentMethod)->jsonSerialize());
    }
}
