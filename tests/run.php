<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use TradeTracker\Sdk\Configuration;
use TradeTracker\Sdk\Exception\AuthenticationRequiredException;
use TradeTracker\Sdk\Http\HttpClientInterface;
use TradeTracker\Sdk\Http\HttpResponse;
use TradeTracker\Sdk\TradeTrackerClient;

final class FakeHttpClient implements HttpClientInterface
{
    /** @var list<array{method:string,url:string,headers:array<string,string>,body:array<string,mixed>}> */
    public array $calls = [];

    public function send(string $method, string $url, array $headers, string $body): HttpResponse
    {
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        $this->calls[] = compact('method', 'url', 'headers') + ['body' => $decoded];

        if (str_ends_with($url, '/authenticate')) {
            return new HttpResponse(200, [], json_encode([
                'data' => ['jwt' => 'test.jwt.token'],
                'success' => true,
            ], JSON_THROW_ON_ERROR));
        }

        if (str_ends_with($url, '/getConversionTransactions')) {
            return new HttpResponse(200, [], json_encode([
                'data' => [[
                    'ID' => 71655,
                    'transactionType' => 'sale',
                    'transactionStatus' => 'accepted',
                    'commission' => 12.5,
                    'currency' => 'EUR',
                ]],
                'success' => true,
            ], JSON_THROW_ON_ERROR));
        }

        return new HttpResponse(404, [], json_encode(['errors' => ['Not found']], JSON_THROW_ON_ERROR));
    }
}

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('Test failed: ' . $message);
    }
}

$transport = new FakeHttpClient();
$client = new TradeTrackerClient(new Configuration(), $transport);

$auth = $client->authenticateWithTradeTrackerId('jane@example.com', 'api-key', demo: true);
expect($auth->jwt === 'test.jwt.token', 'authentication extracts data.jwt');
expect($client->getAccessToken() === 'test.jwt.token', 'authentication stores JWT');
expect($transport->calls[0]['body'] === [
    'tradetracker_id' => 'jane@example.com',
    'api_key' => 'api-key',
    'demo' => true,
], 'authentication serialises documented JSON body');

$response = $client->getConversionTransactions(83809, ['limit' => 10, 'transactionStatus' => 'accepted']);
expect($response->success(), 'success envelope decoded');
expect($response->data()[0]['ID'] === 71655, 'transaction data decoded');
expect($transport->calls[1]['method'] === 'POST', 'operation uses POST');
expect($transport->calls[1]['url'] === 'https://ws.tradetracker.com/rest/affiliate/getConversionTransactions', 'operation uses documented path');
expect($transport->calls[1]['headers']['Authorization'] === 'Bearer test.jwt.token', 'JWT bearer header sent');
expect($transport->calls[1]['body'] === [
    'affiliateSiteID' => 83809,
    'options' => ['limit' => 10, 'transactionStatus' => 'accepted'],
], 'transaction request body matches documented format');

$unauthenticated = new TradeTrackerClient(new Configuration(), new FakeHttpClient());
try {
    $unauthenticated->getAffiliateSiteCategories();
    throw new RuntimeException('Test failed: unauthenticated request should throw.');
} catch (AuthenticationRequiredException) {
    // Expected.
}

echo "All SDK mock tests passed.\n";
