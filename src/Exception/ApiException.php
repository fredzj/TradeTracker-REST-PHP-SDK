<?php

declare(strict_types=1);

namespace TradeTracker\Sdk\Exception;

use RuntimeException;

class ApiException extends RuntimeException
{
    /**
     * @param list<string> $errors
     * @param array<string, mixed>|null $payload
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $errors = [],
        public readonly ?string $errorCode = null,
        public readonly ?array $payload = null,
        public readonly array $headers = [],
    ) {
        $message = $errors[0] ?? sprintf('TradeTracker API request failed with HTTP %d.', $statusCode);
        if ($errorCode !== null) {
            $message .= ' [' . $errorCode . ']';
        }
        parent::__construct($message, $statusCode);
    }
}
