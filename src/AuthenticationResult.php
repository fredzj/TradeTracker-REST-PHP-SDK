<?php

declare(strict_types=1);

namespace TradeTracker\Sdk;

final class AuthenticationResult
{
    public function __construct(
        public readonly string $jwt,
        public readonly ApiResponse $response,
    ) {
    }
}
