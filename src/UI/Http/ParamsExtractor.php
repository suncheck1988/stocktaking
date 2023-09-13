<?php

declare(strict_types=1);

namespace App\UI\Http;

use App\Application\Exception\InvalidArgumentException;
use DateTimeImmutable;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

final class ParamsExtractor
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function fromRequest(ServerRequestInterface $request): self
    {
        $data = $request->getParsedBody();
        if (!\is_array($data)) {
            $data = [];
        }

        return new self($data);
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->data);
    }

    public function getString(string $name): string
    {
        return \array_key_exists($name, $this->data) ? (string)$this->data[$name] : '';
    }

    public function getStringOrNull(string $name): ?string
    {
        return \array_key_exists($name, $this->data) && $this->data[$name] !== null
            ? (string)$this->data[$name]
            : null;
    }

    public function getInt(string $name): int
    {
        return \array_key_exists($name, $this->data) ? (int)$this->data[$name] : 0;
    }

    public function getIntOrNull(string $name): ?int
    {
        return \array_key_exists($name, $this->data) && $this->data[$name] !== null
            ? (int)$this->data[$name]
            : null;
    }

    public function getFloat(string $name): float
    {
        return \array_key_exists($name, $this->data) ? (float)$this->data[$name] : 0.0;
    }

    public function getFloatOrNull(string $name): ?float
    {
        return \array_key_exists($name, $this->data) && $this->data[$name] !== null
            ? (float)$this->data[$name]
            : null;
    }

    /**
     * @param string $name
     * @return ParamsExtractor[]
     */
    public function getArray(string $name): array
    {
        return \array_key_exists($name, $this->data) && \is_array($this->data[$name])
            ? array_map(
                static fn (array $item) => new self($item),
                $this->data[$name]
            )
            : [];
    }

    public function getBool(string $name): bool
    {
        return \array_key_exists($name, $this->data) && $this->data[$name];
    }

    public function getBoolOrNull(string $name): ?bool
    {
        return \array_key_exists($name, $this->data) ? (bool)$this->data[$name] : null;
    }

    public function getSimpleArray(string $name): array
    {
        return \array_key_exists($name, $this->data) && \is_array($this->data[$name]) ? $this->data[$name] : [];
    }

    public function getDateOrNull(string $name): ?DateTimeImmutable
    {
        $value = \array_key_exists($name, $this->data) && $this->data[$name] !== null
            ? (string)$this->data[$name]
            : null;

        if (!$value) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            throw new InvalidArgumentException("Param $name is not date");
        }
    }

    public function getDate(string $name): DateTimeImmutable
    {
        $value = \array_key_exists($name, $this->data) ? (string)$this->data[$name] : '';

        if ($value === '') {
            throw new InvalidArgumentException("Param $name is not date");
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            throw new InvalidArgumentException("Param $name is not date");
        }
    }
}
