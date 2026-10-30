<?php

declare(strict_types=1);

namespace TradeTracker\Sdk\Http;

final class HttpResponse
{
    /** @param array<string, list<string>> $headers */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly string $body,
    ) {
    }
}
