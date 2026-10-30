<?php

declare(strict_types=1);

namespace TradeTracker\Sdk;

use TradeTracker\Sdk\Exception\UnexpectedResponseException;

/**
 * Generic successful TradeTracker response envelope.
 *
 * The API serialises every successful call as {"data": ..., "success": true}.
 */
final class ApiResponse
{
    private function __construct(
        private readonly mixed $data,
        private readonly bool $success,
        private readonly array $raw,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function fromPayload(array $payload): self
    {
        if (!array_key_exists('success', $payload) || $payload['success'] !== true) {
            throw new UnexpectedResponseException('Expected a successful API response envelope.');
        }
        if (!array_key_exists('data', $payload)) {
            throw new UnexpectedResponseException('Successful API response does not contain a data property.');
        }
        return new self($payload['data'], true, $payload);
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function success(): bool
    {
        return $this->success;
    }

    /** @return array<string, mixed> */
    public function raw(): array
    {
        return $this->raw;
    }
}
