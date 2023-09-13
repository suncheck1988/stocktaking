<?php

declare(strict_types=1);

namespace App\Order\Command\Order\Create;

use App\Application\ValueObject\Amount;
use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Client\Repository\CounterpartyRepository;
use App\Client\Repository\EmployeeRepository;
use App\Data\Flusher;
use App\Order\Model\Order\Order;
use App\Order\Repository\DeliveryTypeRepository;
use App\Order\Repository\OrderRepository;
use App\Order\Repository\PaymentMethodRepository;
use App\Order\Specification\Order\UniqueOrderNumberSpecification;
use App\Store\Repository\PositionRepository;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;

class Handler
{
    public function __construct(
        private readonly AuthContext                    $authContext,
        private readonly OrderRepository                $orderRepository,
        private readonly PaymentMethodRepository        $paymentMethodRepository,
        private readonly DeliveryTypeRepository         $deliveryTypeRepository,
        private readonly PositionRepository             $positionRepository,
        private readonly EmployeeRepository             $employeeRepository,
        private readonly CounterpartyRepository         $counterpartyRepository,
        private readonly UniqueOrderNumberSpecification $uniqueOrderNumberSpecification,
        private readonly Flusher                        $flusher,
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(Command $command): void
    {
        $client = $this->authContext->getClient();

        if ($command->getResponsibleUserId() === $client->getUser()->getId()->getValue()) {
            $responsibleUser = $client->getUser();
        } else {
            $responsibleUser = $this->employeeRepository->get(new Uuid($command->getResponsibleUserId()), $client)->getUser();
        }

        $counterparty = null;
        $counterpartyId = $command->getCounterpartyId();
        if ($counterpartyId !== null) {
            $counterparty = $this->counterpartyRepository->get(new Uuid($counterpartyId), $client);
        }

        $deliveryPrice = $command->getDeliveryPrice();

        $date = new DateTimeImmutable();

        $order = new Order(
            Uuid::generate(),
            $client,
            $responsibleUser,
            $counterparty,
            $this->paymentMethodRepository->get(new Uuid($command->getPaymentMethodId()), $client),
            $this->deliveryTypeRepository->get(new Uuid($command->getDeliveryTypeId()), $client),
            $command->getAddress(),
            $command->getComment(),
            $deliveryPrice !== null ? Amount::fromCurrency($deliveryPrice) : null,
            $date,
            $this->uniqueOrderNumberSpecification
        );

        $this->orderRepository->add($order);

        foreach ($command->getItems() as $item) {
            $position = $this->positionRepository->get(new Uuid($item->getId()), $client);

            $order->addOrderItem(
                Uuid::generate(),
                $position,
                Amount::fromCurrency($item->getPrice()),
                $item->getQuantity(),
                $date
            );
        }

        $this->flusher->flush();
    }
}
