<?php

declare(strict_types=1);

namespace App\Store\Command\Vat\Create;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Dto\VatSearchDto;
use App\Store\Model\Vat\Vat;
use App\Store\Repository\VatRepository;
use App\Store\Specification\Vat\DefaultVatSpecification;
use App\Store\Specification\Vat\UniqueVatValueSpecification;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly VatRepository $vatRepository,
        private readonly UniqueVatValueSpecification $uniqueVatValueSpecification,
        private readonly DefaultVatSpecification $defaultVatSpecification,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function handle(Command $command): void
    {
        $client = $this->authContext->getClient();

        if ($command->isDefault()) {
            $searchDto = new VatSearchDto();
            $searchDto->isDefault = true;

            $vats = $this->vatRepository->fetchAll($this->authContext->getClient(), $searchDto);
            foreach ($vats as $item) {
                $item->changeDefault(false);
            }
        }

        $vat = new Vat(
            Uuid::generate(),
            $client,
            $command->getValue(),
            new DateTimeImmutable(),
            $this->uniqueVatValueSpecification,
            $this->defaultVatSpecification
        );

        $this->vatRepository->add($vat);

        $this->flusher->flush();
    }
}
