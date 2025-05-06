<?php

declare(strict_types=1);

namespace App\Consumer\Handler;

class BuildMessage
{
    private array $payload;
    private ?string $routingKey;
    private array $properties;
    private ?array $headers;
    private ?string $error = null;

    public function __construct(
        array $payload,
        ?string $routingKey = null,
        array $properties = [],
        ?array $headers = null
    ) {
        $this->payload = $payload;
        $this->routingKey = $routingKey;
        $this->properties = $properties;
        $this->headers = $headers;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function replacePayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getRoutingKey(): ?string
    {
        return $this->routingKey;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    public function toJson(): string
    {
        $data = [
            'p' => $this->payload,
        ];

        if (null !== $this->error) {
            $data['error'] = $this->error;
        }

        return json_encode($data);
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public static function fromJson($serialized): self
    {
        $data = json_decode($serialized, true);

        return new self($data['p'], $data['error'] ?? null);
    }
}