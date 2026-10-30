<?php

declare(strict_types=1);

namespace TradeTracker\Sdk;

use InvalidArgumentException;

final class Configuration
{
    public function __construct(
        public readonly string $baseUrl = 'https://ws.tradetracker.com',
        public readonly int $connectTimeout = 10,
        public readonly int $timeout = 30,
        public readonly bool $verifyTls = true,
        public readonly string $userAgent = 'tradetracker-rest-php-sdk/1.0.0',
    ) {
        if ($this->connectTimeout < 1 || $this->timeout < 1) {
            throw new InvalidArgumentException('Timeouts must be positive integers.');
        }
        if ($this->baseUrl === '') {
            throw new InvalidArgumentException('baseUrl cannot be empty.');
        }
    }

    public function endpointUrl(string $operation): string
    {
        return rtrim($this->baseUrl, '/') . '/rest/affiliate/' . rawurlencode($operation);
    }
}
