<?php

declare(strict_types=1);

namespace TradeTracker\Sdk;

use InvalidArgumentException;
use JsonException;
use TradeTracker\Sdk\Exception\ApiException;
use TradeTracker\Sdk\Exception\AuthenticationException;
use TradeTracker\Sdk\Exception\AuthenticationRequiredException;
use TradeTracker\Sdk\Exception\UnexpectedResponseException;
use TradeTracker\Sdk\Http\CurlHttpClient;
use TradeTracker\Sdk\Http\HttpClientInterface;
use TradeTracker\Sdk\Http\HttpResponse;

/**
 * Complete client for all operations documented in the bundled OpenAPI file.
 *
 * Filter arguments are associative arrays using the field names from the
 * corresponding OpenAPI *Filter schema. Successful data stays lossless as
 * decoded PHP arrays in ApiResponse::data().
 */
final class TradeTrackerClient
{
    private const MATERIAL_OUTPUT_TYPES = ['html', 'javascript', 'iframe', 'popup', 'popunder', 'rss'];

    private ?string $accessToken = null;

    public function __construct(
        private readonly Configuration $configuration = new Configuration(),
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new CurlHttpClient($this->configuration);
    }

    private readonly HttpClientInterface $httpClient;

    public function setAccessToken(string $jwt): self
    {
        if (trim($jwt) === '') {
            throw new InvalidArgumentException('JWT cannot be empty.');
        }
        $this->accessToken = $jwt;
        return $this;
    }

    public function clearAccessToken(): self
    {
        $this->accessToken = null;
        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Authenticate from a raw OpenAPI request body.
     *
     * @param array<string, mixed> $credentials
     */
    public function authenticate(array $credentials): AuthenticationResult
    {
        $hasTradeTrackerCredentials = isset($credentials['tradetracker_id'], $credentials['api_key'])
            && $credentials['tradetracker_id'] !== '' && $credentials['api_key'] !== '';
        $hasUsernameCredentials = isset($credentials['username'], $credentials['password'])
            && $credentials['username'] !== '' && $credentials['password'] !== '';

        if ($hasTradeTrackerCredentials === $hasUsernameCredentials) {
            throw new InvalidArgumentException(
                'Provide exactly one credential pair: tradetracker_id/api_key or username/password.'
            );
        }

        $response = $this->call('authenticate', $credentials, false);
        $data = $response->data();
        if (!is_array($data) || !isset($data['jwt']) || !is_string($data['jwt']) || $data['jwt'] === '') {
            throw new UnexpectedResponseException('Authentication response does not contain a non-empty data.jwt.');
        }

        $this->accessToken = $data['jwt'];
        return new AuthenticationResult($data['jwt'], $response);
    }

    public function authenticateWithTradeTrackerId(
        string $tradetrackerId,
        string $apiKey,
        ?bool $sandbox = null,
        ?string $locale = null,
        ?bool $demo = null,
    ): AuthenticationResult {
        $body = ['tradetracker_id' => $tradetrackerId, 'api_key' => $apiKey];
        if ($sandbox !== null) { $body['sandbox'] = $sandbox; }
        if ($locale !== null) { $body['locale'] = $locale; }
        if ($demo !== null) { $body['demo'] = $demo; }
        return $this->authenticate($body);
    }

    public function authenticateWithUsernamePassword(
        string $username,
        string $password,
        ?bool $sandbox = null,
        ?string $locale = null,
        ?bool $demo = null,
    ): AuthenticationResult {
        $body = ['username' => $username, 'password' => $password];
        if ($sandbox !== null) { $body['sandbox'] = $sandbox; }
        if ($locale !== null) { $body['locale'] = $locale; }
        if ($demo !== null) { $body['demo'] = $demo; }
        return $this->authenticate($body);
    }

    /** @return ApiResponse<array<int, array<string, mixed>>> */
    public function getAffiliateSiteCategories(): ApiResponse
    {
        return $this->call('getAffiliateSiteCategories', []);
    }

    /** @param array<string, mixed> $options */
    public function getAffiliateSites(array $options = []): ApiResponse
    {
        return $this->call('getAffiliateSites', ['options' => $options]);
    }

    public function getAffiliateSiteTypes(): ApiResponse
    {
        return $this->call('getAffiliateSiteTypes', []);
    }

    public function getAttributions(int $conversionTransactionID): ApiResponse
    {
        return $this->call('getAttributions', ['conversionTransactionID' => $this->nonNegative($conversionTransactionID, 'conversionTransactionID')]);
    }

    public function getCampaignCategories(): ApiResponse
    {
        return $this->call('getCampaignCategories', []);
    }

    public function getCampaignCommissionExtended(int $affiliateSiteID, int $campaignID): ApiResponse
    {
        return $this->call('getCampaignCommissionExtended', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'campaignID' => $this->nonNegative($campaignID, 'campaignID'),
        ]);
    }

    /** @param array<string, mixed> $options */
    public function getCampaignNewsItems(array $options = []): ApiResponse
    {
        return $this->call('getCampaignNewsItems', ['options' => $options]);
    }

    /** @param array<string, mixed> $options */
    public function getCampaigns(int $affiliateSiteID, array $options = []): ApiResponse
    {
        return $this->call('getCampaigns', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'options' => $options,
        ]);
    }

    /** @param array<string, mixed> $options */
    public function getClickTransactions(int $affiliateSiteID, array $options = []): ApiResponse
    {
        return $this->call('getClickTransactions', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'options' => $options,
        ]);
    }

    /** @param array<string, mixed> $options */
    public function getConversionTransactions(int $affiliateSiteID, array $options = []): ApiResponse
    {
        return $this->call('getConversionTransactions', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'options' => $options,
        ]);
    }

    public function getFeedProductCategories(int $affiliateSiteID, int $feedID): ApiResponse
    {
        return $this->call('getFeedProductCategories', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'feedID' => $this->nonNegative($feedID, 'feedID'),
        ]);
    }

    /** @param array<string, mixed> $options */
    public function getFeedProducts(int $affiliateSiteID, array $options = []): ApiResponse
    {
        return $this->call('getFeedProducts', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'options' => $options,
        ]);
    }

    /** @param array<string, mixed> $options */
    public function getFeeds(int $affiliateSiteID, array $options = []): ApiResponse
    {
        return $this->call('getFeeds', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'options' => $options,
        ]);
    }

    public function getMaterialBannerDimensions(): ApiResponse
    {
        return $this->call('getMaterialBannerDimensions', []);
    }

    /** @param array<string, mixed> $options */
    public function getMaterialBannerFlashItems(int $affiliateSiteID, string $materialOutputType, array $options = []): ApiResponse
    {
        return $this->materialCall('getMaterialBannerFlashItems', $affiliateSiteID, $materialOutputType, $options);
    }

    /** @param array<string, mixed> $options */
    public function getMaterialBannerImageItems(int $affiliateSiteID, string $materialOutputType, array $options = []): ApiResponse
    {
        return $this->materialCall('getMaterialBannerImageItems', $affiliateSiteID, $materialOutputType, $options);
    }

    /** @param array<string, mixed> $options */
    public function getMaterialHTMLItems(int $affiliateSiteID, string $materialOutputType, array $options = []): ApiResponse
    {
        return $this->materialCall('getMaterialHTMLItems', $affiliateSiteID, $materialOutputType, $options);
    }

    /** @param array<string, mixed> $options */
    public function getMaterialIncentiveOfferItems(int $affiliateSiteID, string $materialOutputType, array $options = []): ApiResponse
    {
        return $this->materialCall('getMaterialIncentiveOfferItems', $affiliateSiteID, $materialOutputType, $options);
    }

    /** @param array<string, mixed> $options */
    public function getMaterialIncentiveVoucherItems(int $affiliateSiteID, string $materialOutputType, array $options = []): ApiResponse
    {
        return $this->materialCall('getMaterialIncentiveVoucherItems', $affiliateSiteID, $materialOutputType, $options);
    }

    /** @param array<string, mixed> $options */
    public function getMaterialTextItems(int $affiliateSiteID, string $materialOutputType, array $options = []): ApiResponse
    {
        return $this->materialCall('getMaterialTextItems', $affiliateSiteID, $materialOutputType, $options);
    }

    /** @param array<string, mixed> $options */
    public function getPayments(array $options = []): ApiResponse
    {
        return $this->call('getPayments', ['options' => $options]);
    }

    /** @param array<string, mixed> $options */
    public function getReportAffiliateSite(int $affiliateSiteID, array $options = []): ApiResponse
    {
        return $this->call('getReportAffiliateSite', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'options' => $options,
        ]);
    }

    /** @param array<string, mixed> $options */
    public function getReportAffiliateSites(string $affiliateSiteIDs, array $options = []): ApiResponse
    {
        if (trim($affiliateSiteIDs) === '') {
            throw new InvalidArgumentException('affiliateSiteIDs cannot be empty.');
        }
        return $this->call('getReportAffiliateSites', ['affiliateSiteIDs' => $affiliateSiteIDs, 'options' => $options]);
    }

    /** @param array<string, mixed> $options */
    public function getReportCampaign(int $affiliateSiteID, array $options = []): ApiResponse
    {
        return $this->call('getReportCampaign', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'options' => $options,
        ]);
    }

    /** @param array<string, mixed> $options */
    public function getReportReference(int $affiliateSiteID, array $options = []): ApiResponse
    {
        return $this->call('getReportReference', [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'options' => $options,
        ]);
    }

    public function getTouchpoints(int $conversionTransactionID): ApiResponse
    {
        return $this->call('getTouchpoints', ['conversionTransactionID' => $this->nonNegative($conversionTransactionID, 'conversionTransactionID')]);
    }

    /**
     * Generic escape hatch for a documented operation. Use the specific methods above where possible.
     *
     * @param array<string, mixed> $body
     */
    public function call(string $operation, array $body = [], bool $requiresAuthentication = true): ApiResponse
    {
        if ($requiresAuthentication && $this->accessToken === null) {
            throw new AuthenticationRequiredException('Authenticate first or call setAccessToken() with a valid JWT.');
        }

        try {
            $encodedBody = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Request body cannot be encoded as JSON: ' . $exception->getMessage(), 0, $exception);
        }

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        if ($requiresAuthentication) {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        $response = $this->httpClient->send('POST', $this->configuration->endpointUrl($operation), $headers, $encodedBody);
        $payload = $this->decodePayload($response);
        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            $errors = isset($payload['errors']) && is_array($payload['errors'])
                ? array_values(array_map('strval', $payload['errors']))
                : [];
            $errorCode = isset($payload['error_code']) && is_string($payload['error_code']) ? $payload['error_code'] : null;
            if ($operation === 'authenticate' || $response->statusCode === 401) {
                throw new AuthenticationException($response->statusCode, $errors, $errorCode, $payload, $response->headers);
            }
            throw new ApiException($response->statusCode, $errors, $errorCode, $payload, $response->headers);
        }

        return ApiResponse::fromPayload($payload);
    }

    /** @param array<string, mixed> $options */
    private function materialCall(string $operation, int $affiliateSiteID, string $materialOutputType, array $options): ApiResponse
    {
        if (!in_array($materialOutputType, self::MATERIAL_OUTPUT_TYPES, true)) {
            throw new InvalidArgumentException(
                'materialOutputType must be one of: ' . implode(', ', self::MATERIAL_OUTPUT_TYPES) . '.'
            );
        }
        return $this->call($operation, [
            'affiliateSiteID' => $this->nonNegative($affiliateSiteID, 'affiliateSiteID'),
            'materialOutputType' => $materialOutputType,
            'options' => $options,
        ]);
    }

    private function nonNegative(int $value, string $name): int
    {
        if ($value < 0) {
            throw new InvalidArgumentException($name . ' must be a non-negative integer.');
        }
        return $value;
    }

    /** @return array<string, mixed> */
    private function decodePayload(HttpResponse $response): array
    {
        if ($response->body === '') {
            throw new UnexpectedResponseException('API returned an empty response body.');
        }
        try {
            $payload = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedResponseException('API returned invalid JSON: ' . $exception->getMessage(), 0, $exception);
        }
        if (!is_array($payload)) {
            throw new UnexpectedResponseException('API response JSON must be an object.');
        }
        return $payload;
    }
}
