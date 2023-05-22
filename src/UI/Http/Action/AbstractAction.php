<?php

declare(strict_types=1);

namespace App\UI\Http\Action;

use App\Application\Service\Validator\Validator;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\User;
use App\Auth\Service\AuthContext;
use Assert\AssertionFailedException;
use InvalidArgumentException;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpUnauthorizedException;

abstract class AbstractAction
{
    protected ?ServerRequestInterface $request = null;
    protected ?ResponseInterface $response = null;

    protected Validator $validator;
    protected CommandBus $bus;
    protected AuthContext $authContext;
    protected LoggerInterface $logger;

    /**
     * @var array<string, string>
     */
    private array $args = [];

    public function __construct()
    {
    }

    public function init(
        Validator $validator,
        CommandBus $bus,
        AuthContext $authContext,
        LoggerInterface $logger
    ): void {
        $this->validator = $validator;
        $this->bus = $bus;
        $this->authContext = $authContext;
        $this->logger = $logger;
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        return $this->handle($request);
    }

    abstract public function handle(ServerRequestInterface $request): ResponseInterface;

    protected function resolveArg(string $name): string
    {
        if (!isset($this->args[$name])) {
            throw new InvalidArgumentException("Could not resolve argument `$name`.");
        }

        return $this->args[$name];
    }

    /**
     * @param array<string, string> $headers
     */
    protected function asJson(array $data, int $status = 200, array $headers = []): ResponseInterface
    {
        $json = json_encode(
            $data,
            getenv('APP_ENV') === 'dev' ? JSON_PRETTY_PRINT : 0
        );

        /** @var ResponseInterface $response */
        $response = $this->response;
        $response->getBody()->write($json);

        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /**
     * @param array<string, string> $headers
     */
    protected function asJsonString(string $json, int $status = 200, array $headers = []): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->response;

        $response->getBody()->write($json);

        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    protected function asHtml(string $html, int $status = 200): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->response;
        $response->getBody()->write($html);

        return $response
            ->withHeader('Content-Type', 'text/html')
            ->withStatus($status);
    }

    /**
     * @param array<string, string> $headers
     */
    protected function asPdf(string $body, int $status = 200, array $headers = []): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->response;

        $response->getBody()->write($body);

        $response = $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withStatus($status);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /**
     * @param array<string, string> $headers
     */
    protected function asCsv(string $body, int $status = 200, array $headers = []): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->response;

        $response->getBody()->write($body);

        $response = $response
            ->withHeader('Content-Type', 'text/csv')
            ->withStatus($status);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    protected function asEmpty(int $status = 204): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->response;
        return $response->withStatus($status);
    }

    protected function denyAccessNotAuthenticated(): void
    {
        if (!$this->authContext->isAuthenticated()) {
            /** @var ServerRequestInterface $request */
            $request = $this->request;
            throw new HttpUnauthorizedException($request);
        }
    }

    protected function denyAccessNotClient(): void
    {
        $this->denyAccessNotAuthenticated();
        if (!$this->getCurrentUser()->getRole()->isClient()) {
            /** @var ServerRequestInterface $request */
            $request = $this->request;
            throw new HttpUnauthorizedException($request);
        }
    }

    /**
     * @throws AssertionFailedException
     */
    protected function denyAccessNotClientAndCheckEmployee(string $employeeId): void
    {
        $this->denyAccessNotClient();

        $this->authContext->checkCurrentClientEmployee($employeeId);
    }

    /**
     * @param int[] $permissions
     * @param int[] $roles
     * @throws AssertionFailedException
     */
    protected function denyAccessNotHasPermissionsByRoles(array $permissions, array $roles): void
    {
        $this->denyAccessNotAuthenticated();

        /** @var User $currentUser */
        $currentUser = $this->authContext->getCurrentUser();

        if (!\in_array($currentUser->getRole()->getValue(), $roles, true)) {
            /** @var ServerRequestInterface $request */
            $request = $this->request;
            throw new HttpUnauthorizedException($request);
        }

        $isHasPermission = false;
        foreach ($permissions as $permission) {
            if ($this->getCurrentUser()->hasPermission(new Permission($permission))) {
                $isHasPermission = true;
                break;
            }
        }

        if (!$isHasPermission) {
            /** @var ServerRequestInterface $request */
            $request = $this->request;
            throw new HttpUnauthorizedException($request);
        }
    }

    protected function getCurrentUser(): User
    {
        $currentUser = $this->authContext->getCurrentUser();
        if ($currentUser === null) {
            /** @var ServerRequestInterface $request */
            $request = $this->request;
            throw new HttpUnauthorizedException($request);
        }

        return $currentUser;
    }
}
