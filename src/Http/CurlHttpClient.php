<?php

declare(strict_types=1);

namespace TradeTracker\Sdk\Http;

use TradeTracker\Sdk\Configuration;
use TradeTracker\Sdk\Exception\TransportException;

final class CurlHttpClient implements HttpClientInterface
{
    public function __construct(private readonly Configuration $configuration)
    {
        if (!function_exists('curl_init')) {
            throw new TransportException('The PHP cURL extension is required. Install or enable ext-curl.');
        }
    }

    /** @param array<string, string> $headers */
    public function send(string $method, string $url, array $headers, string $body): HttpResponse
    {
        $handle = curl_init($url);
        if ($handle === false) {
            throw new TransportException('Unable to initialise a cURL request.');
        }

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_CONNECTTIMEOUT => $this->configuration->connectTimeout,
            CURLOPT_TIMEOUT => $this->configuration->timeout,
            CURLOPT_USERAGENT => $this->configuration->userAgent,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => $this->configuration->verifyTls,
            CURLOPT_SSL_VERIFYHOST => $this->configuration->verifyTls ? 2 : 0,
        ]);

        $raw = curl_exec($handle);
        if ($raw === false) {
            $message = curl_error($handle);
            $code = curl_errno($handle);
            curl_close($handle);
            throw new TransportException(sprintf('cURL transport error %d: %s', $code, $message));
        }

        $headerSize = (int) curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        return new HttpResponse(
            $statusCode,
            $this->parseHeaders(substr($raw, 0, $headerSize)),
            substr($raw, $headerSize),
        );
    }

    /** @return array<string, list<string>> */
    private function parseHeaders(string $headerBlock): array
    {
        $headers = [];
        foreach (preg_split('/\r\n|\n|\r/', trim($headerBlock)) ?: [] as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }
            [$name, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($name))][] = trim($value);
        }
        return $headers;
    }
}
