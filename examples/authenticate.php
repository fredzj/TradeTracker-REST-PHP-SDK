<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use TradeTracker\Sdk\Exception\ApiException;
use TradeTracker\Sdk\TradeTrackerClient;

$tradetrackerId = getenv('TRADETRACKER_ID') ?: '';
$apiKey = getenv('TRADETRACKER_API_KEY') ?: '';
$demo = filter_var(getenv('TRADETRACKER_DEMO') ?: false, FILTER_VALIDATE_BOOL);

if ($tradetrackerId === '' || $apiKey === '') {
    fwrite(STDERR, "Stel TRADETRACKER_ID en TRADETRACKER_API_KEY in.\n");
    exit(2);
}

try {
    $client = new TradeTrackerClient();
    $result = $client->authenticateWithTradeTrackerId(
        tradetrackerId: $tradetrackerId,
        apiKey: $apiKey,
        locale: 'nl_NL',
        demo: $demo,
    );

    // Log nooit de volledige JWT in productie.
    printf("Aanmelding gelukt; JWT ontvangen (%d tekens).\n", strlen($result->jwt));
} catch (ApiException $exception) {
    fwrite(STDERR, sprintf("API-fout (%d): %s\n", $exception->statusCode, $exception->getMessage()));
    exit(1);
} catch (Throwable $exception) {
    fwrite(STDERR, "Fout: " . $exception->getMessage() . "\n");
    exit(1);
}
