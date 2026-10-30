# API-dekking

Iedere operatie uit `openapi/openapi.yaml` heeft een gelijknamige methode op `TradeTracker\Sdk\TradeTrackerClient`. Het pad en HTTP-werkwoord zijn door de SDK centraal afgedwongen: alle calls gaan via `POST https://ws.tradetracker.com/rest/affiliate/{operation}` met `Content-Type: application/json`.

| OpenAPI operationId | Clientmethode | JSON-body |
|---|---|---|
| `authenticate` | `authenticate`, `authenticateWithTradeTrackerId`, `authenticateWithUsernamePassword` | Credentials en optionele `sandbox`, `locale`, `demo` |
| `getAffiliateSiteCategories` | `getAffiliateSiteCategories` | `{}` |
| `getAffiliateSites` | `getAffiliateSites` | `options` |
| `getAffiliateSiteTypes` | `getAffiliateSiteTypes` | `{}` |
| `getAttributions` | `getAttributions` | `conversionTransactionID` |
| `getCampaignCategories` | `getCampaignCategories` | `{}` |
| `getCampaignCommissionExtended` | `getCampaignCommissionExtended` | `affiliateSiteID`, `campaignID` |
| `getCampaignNewsItems` | `getCampaignNewsItems` | `options` |
| `getCampaigns` | `getCampaigns` | `affiliateSiteID`, `options` |
| `getClickTransactions` | `getClickTransactions` | `affiliateSiteID`, `options` |
| `getConversionTransactions` | `getConversionTransactions` | `affiliateSiteID`, `options` |
| `getFeedProductCategories` | `getFeedProductCategories` | `affiliateSiteID`, `feedID` |
| `getFeedProducts` | `getFeedProducts` | `affiliateSiteID`, `options` |
| `getFeeds` | `getFeeds` | `affiliateSiteID`, `options` |
| `getMaterialBannerDimensions` | `getMaterialBannerDimensions` | `{}` |
| `getMaterialBannerFlashItems` | `getMaterialBannerFlashItems` | `affiliateSiteID`, `materialOutputType`, `options` |
| `getMaterialBannerImageItems` | `getMaterialBannerImageItems` | `affiliateSiteID`, `materialOutputType`, `options` |
| `getMaterialHTMLItems` | `getMaterialHTMLItems` | `affiliateSiteID`, `materialOutputType`, `options` |
| `getMaterialIncentiveOfferItems` | `getMaterialIncentiveOfferItems` | `affiliateSiteID`, `materialOutputType`, `options` |
| `getMaterialIncentiveVoucherItems` | `getMaterialIncentiveVoucherItems` | `affiliateSiteID`, `materialOutputType`, `options` |
| `getMaterialTextItems` | `getMaterialTextItems` | `affiliateSiteID`, `materialOutputType`, `options` |
| `getPayments` | `getPayments` | `options` |
| `getReportAffiliateSite` | `getReportAffiliateSite` | `affiliateSiteID`, `options` |
| `getReportAffiliateSites` | `getReportAffiliateSites` | `affiliateSiteIDs`, `options` |
| `getReportCampaign` | `getReportCampaign` | `affiliateSiteID`, `options` |
| `getReportReference` | `getReportReference` | `affiliateSiteID`, `options` |
| `getTouchpoints` | `getTouchpoints` | `conversionTransactionID` |

`options` is altijd een associatieve array. Alleen de velden van het corresponderende filtertype uit `openapi/openapi.yaml` zijn geldig.
