# TradeTracker REST PHP SDK

Een zelfstandige PHP 8.1+-client voor de TradeTracker Web Services REST API. De SDK dekt alle 27 operaties uit de meegeleverde OpenAPI 3.1-specificatie. Alle aanroepen gebruiken `POST`, JSON-body’s en de `{data, success}`-response-envelop van de API.

## Installatie

Plaats dit pakket in uw project en installeer de autoloader:

```bash
composer install
```

De SDK heeft alleen PHP met de extensies `curl` en `json` nodig. Voeg bij gebruik vanuit een ander Composer-project de map als repository toe of publiceer het pakket naar uw eigen Composer-registry.

## Authenticatie met TradeTracker ID en API-sleutel

Gebruik de TradeTracker ID-e-mail en de REST API-sleutel uit de Web Services-pagina van het juiste account. De client bewaart de JWT na een geslaagde aanmelding automatisch en stuurt die als `Authorization: Bearer <jwt>` mee bij alle volgende calls.

```php
use TradeTracker\Sdk\TradeTrackerClient;

$client = new TradeTrackerClient();
$auth = $client->authenticateWithTradeTrackerId(
    tradetrackerId: 'jane@example.com',
    apiKey: 'uw-rest-api-sleutel',
    locale: 'nl_NL',
    demo: false,
);

printf("JWT ontvangen (%d tekens)\n", strlen($auth->jwt));
```

De API ondersteunt daarnaast `authenticateWithUsernamePassword()` voor accounts die nog niet aan een TradeTracker ID gekoppeld zijn. Een JWT is volgens de API-documentatie 24 uur geldig. Bij een HTTP 401 meldt u zich opnieuw aan.

## Conversie- en kliktransacties ophalen

```php
$response = $client->getConversionTransactions(
    affiliateSiteID: 83809,
    options: [
        'registrationDateFrom' => '2026-10-01T00:00:00+02:00',
        'registrationDateTo' => '2026-10-31T23:59:59+01:00',
        'transactionStatus' => 'accepted',
        'limit' => 100,
        'offset' => 0,
    ],
);

foreach ($response->data() as $transaction) {
    printf(
        "%s | %s | %s | %.2f %s\n",
        $transaction['ID'],
        $transaction['transactionType'],
        $transaction['transactionStatus'],
        $transaction['commission'],
        $transaction['currency'],
    );
}
```

Voor kliktransacties gebruikt u `getClickTransactions($affiliateSiteID, $options)`. De geretourneerde `ApiResponse` bevat `data()`, `success()` en `raw()`; de inhoud van `data()` is een associatieve array met exact de velden uit de OpenAPI-specificatie.

## Uitvoerbare voorbeelden

Gebruik omgevingsvariabelen om geheimen buiten uw broncode te houden:

```bash
export TRADETRACKER_ID='jane@example.com'
export TRADETRACKER_API_KEY='uw-rest-api-sleutel'
export TRADETRACKER_AFFILIATE_SITE_ID='83809'

php examples/authenticate.php
php examples/get_transactions.php
```

`examples/authenticate.php` logt in en toont alleen de lengte van de JWT. `examples/get_transactions.php` logt in en haalt conversie- en kliktransacties op. Stel desgewenst `TRADETRACKER_DEMO=true` in voor demo-data.

## Foutafhandeling

| Situatie | Exception |
|---|---|
| Netwerk- of cURL-fout | `TransportException` |
| Ongeldige credentials of JWT | `AuthenticationException` |
| Beschermde operatie zonder JWT | `AuthenticationRequiredException` |
| Andere HTTP-fout | `ApiException` |
| Onverwachte of ongeldige JSON-response | `UnexpectedResponseException` |

`ApiException` bevat `statusCode`, `errors`, `errorCode`, `payload` en `headers`. Hierdoor kunt u API-fouten veilig loggen of gericht afhandelen.

## Volledige operationele dekking

| Domein | Methoden |
|---|---|
| Authenticatie | `authenticate`, `authenticateWithTradeTrackerId`, `authenticateWithUsernamePassword` |
| Affiliatesites | `getAffiliateSiteCategories`, `getAffiliateSites`, `getAffiliateSiteTypes` |
| Campagnes | `getCampaignCategories`, `getCampaignCommissionExtended`, `getCampaignNewsItems`, `getCampaigns` |
| Transacties | `getAttributions`, `getClickTransactions`, `getConversionTransactions`, `getTouchpoints` |
| Productfeeds | `getFeedProductCategories`, `getFeedProducts`, `getFeeds` |
| Materialen | `getMaterialBannerDimensions`, `getMaterialBannerFlashItems`, `getMaterialBannerImageItems`, `getMaterialHTMLItems`, `getMaterialIncentiveOfferItems`, `getMaterialIncentiveVoucherItems`, `getMaterialTextItems` |
| Betalingen | `getPayments` |
| Rapportages | `getReportAffiliateSite`, `getReportAffiliateSites`, `getReportCampaign`, `getReportReference` |

## Transport en veiligheid

De standaardserver is `https://ws.tradetracker.com` en TLS-certificaten worden door cURL geverifieerd. Pas alleen `Configuration` aan wanneer u een expliciet andere API-host of afwijkende time-outs nodig heeft. Zet `verifyTls` niet uit in productie.

De complete bronspecificatie staat in [openapi/openapi.yaml](openapi/openapi.yaml). Deze SDK gebruikt de concrete paden, JSON-requestbodies, JWT-header en response-envelop die in de voorbeeldscripts zijn bevestigd.

## Lokale controle

De pakkettest gebruikt een lokale mock-HTTP-client; er worden geen externe API-calls of credentials gebruikt:

```bash
php tests/run.php
# of, na composer install:
composer test
```
