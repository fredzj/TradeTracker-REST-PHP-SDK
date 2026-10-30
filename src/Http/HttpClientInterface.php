<?php

declare(strict_types=1);

namespace TradeTracker\Sdk\Http;

interface HttpClientInterface
{
    /**
     * @param array<string, string> $headers
     */
    public function send(string $method, string $url, array $headers, string $body): HttpResponse;
}
