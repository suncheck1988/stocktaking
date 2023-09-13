<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Application\Exception\DomainException;
use App\Application\Exception\InvalidArgumentException;
use App\Application\Interface\ClientAwareInterface;
use App\Application\Interface\ClientAwareTrait;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\User;
use App\Auth\Repository\UserRepository;
use App\Client\Repository\EmployeeRepository;
use App\Client\Service\Client\ClientFinder;
use App\Data\RedisWrapper;
use Assert\AssertionFailedException;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class AuthContext implements ClientAwareInterface
{
    use ClientAwareTrait;

    private AuthTokenManager $authTokenManager;
    private UserRepository $userRepository;
    private EmployeeRepository $employeeRepository;
    private ClientFinder $clientFinder;
    private bool $isAuthenticated = false;
    private ?User $currentUser = null;
    private ?string $currentToken = null;

    public function __construct(
        AuthTokenManager $authTokenManager,
        UserRepository $userRepository,
        EmployeeRepository $employeeRepository,
        ClientFinder $clientFinder,
        private readonly RedisWrapper $redis
    ) {
        $this->authTokenManager = $authTokenManager;
        $this->userRepository = $userRepository;
        $this->employeeRepository = $employeeRepository;
        $this->clientFinder = $clientFinder;
    }

    public function handleRequest(ServerRequestInterface $request): void
    {
        $token = $request->getHeaderLine('Authorization');
        $token = str_replace(['Bearer', ' '], '', $token);

        if ($token) {
            $data = null;
            try {
                $data = $this->authTokenManager->decode($token);
            } catch (Exception|Throwable) {
            }

            if (\is_array($data) && isset($data['id'])) {
                try {
                    $currentUser = $this->userRepository->get(new Uuid((string)$data['id']));

                    if ($currentUser->getStatus()->isBlocked()) {
                        throw new DomainException('Пользователь заблокирован, обратитесь к администратору');
                    }

                    if (!$currentUser->getStatus()->isActive()) {
                        return;
                    }

                    $this->currentUser = $currentUser;
                    if ($this->currentUser->getRole()->isClient() || $this->currentUser->getRole()->isClientEmployee()) {
                        $this->setClient();
                    }
                } catch (InvalidArgumentException|AssertionFailedException) {
                    return;
                }

                /** @todo проверять срок жизни токена */
                $this->isAuthenticated = true;
                $this->currentToken = $token;

                $this->ensureDdos();
            }
        }
    }

    public function isAuthenticated(): bool
    {
        return $this->isAuthenticated;
    }

    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }

    /**
     * @throws AssertionFailedException
     */
    public function checkCurrentClientEmployee(string $employeeId): void
    {
        $employee = $this->employeeRepository->getById(new Uuid($employeeId));

        /** @var User $currentUser */
        $currentUser = $this->currentUser;

        if ($currentUser->getId()->getValue() !== $employee->getClient()->getUser()->getId()->getValue()) {
            throw new DomainException('Сотрудник не принадлежит текущему клиенту');
        }
    }

//    public function getCurrentToken(): ?string
//    {
//        return $this->currentToken;
//    }

//    public function isConsole(): bool
//    {
//        return \defined('APP_CONSOLE') && APP_CONSOLE === true;
//    }

    private function ensureDdos(): void
    {
        if ($this->currentUser === null) {
            return;
        }

        $key = 'user_ddos_' . $this->currentUser->getId();
        if ($this->redis->exists($key)) {
            $value = (int)$this->redis->get($key);
            if ($value >= 50) {
                throw new InvalidArgumentException('Слишком много запросов. Попробуйте позже');
            }
            $value++;
        } else {
            $value = 1;
        }

        $this->redis->set($key, (string)$value, ['ex' => 5]);
    }
}
