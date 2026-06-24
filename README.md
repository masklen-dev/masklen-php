# masklen PHP SDK

Official PHP SDK for the [masklen.dev](https://masklen.dev) IP intelligence API. Provides IP geolocation, network, privacy, and locale data with zero external dependencies.

## Requirements

- PHP 8.1+
- `ext-curl`
- `ext-json`

## Installation

```bash
composer require masklen/masklen
```

## Quick start

### Look up your own IP

```php
use Masklen\MasklenClient;

$client = new MasklenClient('your-api-key');

$result = $client->lookupSelf();

echo $result->ip;                        // e.g. "203.0.113.42"
echo $result->location?->country_code;   // e.g. "US"
echo $result->network?->isp;             // e.g. "Comcast Cable"
```

### Look up a specific IP

```php
$result = $client->lookup('8.8.8.8');

echo $result->location?->city;          // "Mountain View"
echo $result->privacy?->vpn ? 'VPN' : 'No VPN';
```

### Batch lookup (up to 1000 IPs)

```php
use Masklen\Types\LookupResult;
use Masklen\Types\BatchItemError;

$batch = $client->lookupBatch(['8.8.8.8', '1.1.1.1', '2606:4700:4700::1111']);

foreach ($batch->results as $item) {
    if ($item instanceof LookupResult) {
        echo $item->ip . ' -> ' . ($item->location?->country_code ?? 'unknown') . PHP_EOL;
    } elseif ($item instanceof BatchItemError) {
        echo $item->ip . ' failed: ' . $item->errorMessage . PHP_EOL;
    }
}
```

## Method signatures

```php
// Look up the caller's own IP address
lookupSelf(array $fields = []): LookupResult

// Look up a specific IPv4 or IPv6 address
lookup(string $ip, array $fields = []): LookupResult

// Look up up to 1000 IP addresses in one request
lookupBatch(array $ips, array $fields = []): BatchResult
```

## Filtering fields

All methods accept an optional `$fields` array to request only the data you need. Valid values: `location`, `network`, `privacy`, `locale`. Omitting `$fields` returns all available data.

```php
// Request only location and privacy data
$result = $client->lookup('8.8.8.8', ['location', 'privacy']);

echo $result->location?->country;   // "United States"
echo $result->network;              // null (not requested)
```

## Response models

### LookupResult

| Property    | Type           | Description                       |
|-------------|----------------|-----------------------------------|
| `$ip`       | `string`       | The queried IP address            |
| `$location` | `?Location`    | Geographic location data          |
| `$network`  | `?Network`     | ASN, ISP, and organization data   |
| `$privacy`  | `?Privacy`     | VPN, proxy, Tor, and threat level |
| `$locale`   | `?Locale`      | Currency, languages, calling code |

### Location

| Property       | Type      |
|----------------|-----------|
| `$city`        | `?string` |
| `$region`      | `?string` |
| `$country`     | `?string` |
| `$country_code`| `?string` |
| `$latitude`    | `?float`  |
| `$longitude`   | `?float`  |
| `$postal_code` | `?string` |
| `$timezone`    | `?string` |

### Network

| Property        | Type      |
|-----------------|-----------|
| `$asn`          | `?string` |
| `$isp`          | `?string` |
| `$organization` | `?string` |
| `$domain`       | `?string` |

### Privacy

| Property        | Type     | Notes                    |
|-----------------|----------|--------------------------|
| `$vpn`          | `bool`   |                          |
| `$proxy`        | `bool`   |                          |
| `$tor`          | `bool`   |                          |
| `$hosting`      | `bool`   |                          |
| `$threat_level` | `string` | `"low"` or `"medium"`    |

### Locale

| Property           | Type       |
|--------------------|------------|
| `$currency`        | `?string`  |
| `$currency_symbol` | `?string`  |
| `$calling_code`    | `?string`  |
| `$languages`       | `string[]` |
| `$flag`            | `?string`  |

### BatchResult

| Property   | Type                              |
|------------|-----------------------------------|
| `$results` | `array<LookupResult\|BatchItemError>` |

### BatchItemError

| Property        | Type     |
|-----------------|----------|
| `$ip`           | `string` |
| `$errorCode`    | `string` |
| `$errorMessage` | `string` |

## Error handling

API errors throw `Masklen\Exceptions\MasklenException`, which extends `RuntimeException`.

```php
use Masklen\Exceptions\MasklenException;
use Masklen\MasklenClient;

$client = new MasklenClient('your-api-key');

try {
    $result = $client->lookup('8.8.8.8');
} catch (MasklenException $e) {
    echo 'HTTP status: ' . $e->getStatusCode() . PHP_EOL;   // e.g. 401
    echo 'Error code: '  . $e->getErrorCode() . PHP_EOL;    // e.g. "UNAUTHORIZED"
    echo 'Message: '     . $e->getErrorMessage() . PHP_EOL; // e.g. "Invalid API key."
} catch (\RuntimeException $e) {
    // Network failure or unexpected response
    echo 'Request failed: ' . $e->getMessage() . PHP_EOL;
}
```

## License

MIT
