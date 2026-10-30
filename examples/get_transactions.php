<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use TradeTracker\Sdk\Exception\ApiException;
use TradeTracker\Sdk\TradeTrackerClient;

$tradetrackerId = getenv('TRADETRACKER_ID') ?: '';
$apiKey = getenv('TRADETRACKER_API_KEY') ?: '';
$affiliateSiteID = filter_var(getenv('TRADETRACKER_AFFILIATE_SITE_ID'), FILTER_VALIDATE_INT);
$demo = filter_var(getenv('TRADETRACKER_DEMO') ?: false, FILTER_VALIDATE_BOOL);

if ($tradetrackerId === '' || $apiKey === '' || $affiliateSiteID === false || $affiliateSiteID < 0) {
    fwrite(STDERR, "Stel TRADETRACKER_ID, TRADETRACKER_API_KEY en TRADETRACKER_AFFILIATE_SITE_ID in.\n");
    exit(2);
}

try {
    $client = new TradeTrackerClient();
    $client->authenticateWithTradeTrackerId(
        tradetrackerId: $tradetrackerId,
        apiKey: $apiKey,
        locale: 'nl_NL',
        demo: $demo,
    );

    $conversionTransactions = $client->getConversionTransactions((int) $affiliateSiteID, [
        'registrationDateFrom' => '2026-10-01T00:00:00+02:00',
        'registrationDateTo' => '2026-10-31T23:59:59+01:00',
        'transactionStatus' => 'accepted',
        'limit' => 100,
        'offset' => 0,
    ]);

    echo "Conversietransacties:\n";
    foreach ($conversionTransactions->data() as $transaction) {
        printf(
            "  #%s | %s | %s | %.2f %s\n",
            $transaction['ID'],
            $transaction['transactionType'],
            $transaction['transactionStatus'],
            $transaction['commission'],
            $transaction['currency'],
        );
    }

    $clickTransactions = $client->getClickTransactions((int) $affiliateSiteID, [
        'registrationDateFrom' => '2026-10-01T00:00:00+02:00',
        'registrationDateTo' => '2026-10-31T23:59:59+01:00',
        'sort' => 'registrationDate',
        'sortDirection' => 'descending',
        'limit' => 100,
        'offset' => 0,
    ]);

    echo "Kliktransacties:\n";
    foreach ($clickTransactions->data() as $transaction) {
        printf(
            "  #%s | %s | %.2f %s\n",
            $transaction['ID'],
            $transaction['transactionStatus'],
            $transaction['commission'],
            $transaction['currency'],
        );
    }
} catch (ApiException $exception) {
    fwrite(STDERR, sprintf("API-fout (%d): %s\n", $exception->statusCode, $exception->getMessage()));
    exit(1);
} catch (Throwable $exception) {
    fwrite(STDERR, "Fout: " . $exception->getMessage() . "\n");
    exit(1);
}
