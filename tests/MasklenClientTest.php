<?php

declare(strict_types=1);

namespace Masklen\Tests;

use Masklen\Exceptions\MasklenException;
use Masklen\Types\BatchItemError;
use Masklen\Types\BatchResult;
use Masklen\Types\LookupResult;
use Masklen\Types\Location;
use Masklen\Types\Network;
use Masklen\Types\Privacy;
use Masklen\Types\Locale;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the masklen.dev PHP SDK.
 *
 * These tests cover model deserialization and exception construction directly,
 * without making real HTTP calls.
 */
class MasklenClientTest extends TestCase
{
    // ---------------------------------------------------------------------------
    // Location
    // ---------------------------------------------------------------------------

    public function testLocationFromArrayFull(): void
    {
        $data = [
            'city' => 'New York',
            'region' => 'New York',
            'country' => 'United States',
            'country_code' => 'US',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'postal_code' => '10001',
            'timezone' => 'America/New_York',
        ];

        $location = Location::fromArray($data);

        $this->assertSame('New York', $location->city);
        $this->assertSame('New York', $location->region);
        $this->assertSame('United States', $location->country);
        $this->assertSame('US', $location->country_code);
        $this->assertEqualsWithDelta(40.7128, $location->latitude, 0.0001);
        $this->assertEqualsWithDelta(-74.0060, $location->longitude, 0.0001);
        $this->assertSame('10001', $location->postal_code);
        $this->assertSame('America/New_York', $location->timezone);
    }

    public function testLocationFromArrayEmpty(): void
    {
        $location = Location::fromArray([]);

        $this->assertNull($location->city);
        $this->assertNull($location->region);
        $this->assertNull($location->country);
        $this->assertNull($location->country_code);
        $this->assertNull($location->latitude);
        $this->assertNull($location->longitude);
        $this->assertNull($location->postal_code);
        $this->assertNull($location->timezone);
    }

    // ---------------------------------------------------------------------------
    // Network
    // ---------------------------------------------------------------------------

    public function testNetworkFromArrayFull(): void
    {
        $data = [
            'asn' => 'AS15169',
            'isp' => 'Google LLC',
            'organization' => 'Google LLC',
            'domain' => 'google.com',
        ];

        $network = Network::fromArray($data);

        $this->assertSame('AS15169', $network->asn);
        $this->assertSame('Google LLC', $network->isp);
        $this->assertSame('Google LLC', $network->organization);
        $this->assertSame('google.com', $network->domain);
    }

    public function testNetworkFromArrayEmpty(): void
    {
        $network = Network::fromArray([]);

        $this->assertNull($network->asn);
        $this->assertNull($network->isp);
        $this->assertNull($network->organization);
        $this->assertNull($network->domain);
    }

    // ---------------------------------------------------------------------------
    // Privacy
    // ---------------------------------------------------------------------------

    public function testPrivacyFromArrayFull(): void
    {
        $data = [
            'vpn' => true,
            'proxy' => false,
            'tor' => false,
            'hosting' => true,
            'threat_level' => 'medium',
        ];

        $privacy = Privacy::fromArray($data);

        $this->assertTrue($privacy->vpn);
        $this->assertFalse($privacy->proxy);
        $this->assertFalse($privacy->tor);
        $this->assertTrue($privacy->hosting);
        $this->assertSame('medium', $privacy->threat_level);
    }

    public function testPrivacyDefaultsToLowThreat(): void
    {
        $privacy = Privacy::fromArray([]);

        $this->assertFalse($privacy->vpn);
        $this->assertFalse($privacy->proxy);
        $this->assertFalse($privacy->tor);
        $this->assertFalse($privacy->hosting);
        $this->assertSame('low', $privacy->threat_level);
    }

    // ---------------------------------------------------------------------------
    // Locale
    // ---------------------------------------------------------------------------

    public function testLocaleFromArrayFull(): void
    {
        $data = [
            'currency' => 'USD',
            'currency_symbol' => '$',
            'calling_code' => '+1',
            'languages' => ['en', 'es'],
            'flag' => 'US',
        ];

        $locale = Locale::fromArray($data);

        $this->assertSame('USD', $locale->currency);
        $this->assertSame('$', $locale->currency_symbol);
        $this->assertSame('+1', $locale->calling_code);
        $this->assertSame(['en', 'es'], $locale->languages);
        $this->assertSame('US', $locale->flag);
    }

    public function testLocaleFromArrayEmptyLanguages(): void
    {
        $locale = Locale::fromArray([]);

        $this->assertNull($locale->currency);
        $this->assertSame([], $locale->languages);
    }

    // ---------------------------------------------------------------------------
    // LookupResult
    // ---------------------------------------------------------------------------

    public function testLookupResultFromArrayWithAllFields(): void
    {
        $data = [
            'ip' => '8.8.8.8',
            'location' => [
                'city' => 'Mountain View',
                'country_code' => 'US',
                'latitude' => 37.386,
                'longitude' => -122.0838,
            ],
            'network' => [
                'asn' => 'AS15169',
                'isp' => 'Google LLC',
            ],
            'privacy' => [
                'vpn' => false,
                'proxy' => false,
                'tor' => false,
                'hosting' => true,
                'threat_level' => 'low',
            ],
            'locale' => [
                'currency' => 'USD',
                'languages' => ['en'],
            ],
        ];

        $result = LookupResult::fromArray($data);

        $this->assertSame('8.8.8.8', $result->ip);
        $this->assertInstanceOf(Location::class, $result->location);
        $this->assertSame('Mountain View', $result->location->city);
        $this->assertInstanceOf(Network::class, $result->network);
        $this->assertSame('AS15169', $result->network->asn);
        $this->assertInstanceOf(Privacy::class, $result->privacy);
        $this->assertTrue($result->privacy->hosting);
        $this->assertInstanceOf(Locale::class, $result->locale);
        $this->assertSame('USD', $result->locale->currency);
    }

    public function testLookupResultFromArrayMinimal(): void
    {
        $result = LookupResult::fromArray(['ip' => '1.2.3.4']);

        $this->assertSame('1.2.3.4', $result->ip);
        $this->assertNull($result->location);
        $this->assertNull($result->network);
        $this->assertNull($result->privacy);
        $this->assertNull($result->locale);
    }

    // ---------------------------------------------------------------------------
    // BatchItemError
    // ---------------------------------------------------------------------------

    public function testBatchItemErrorFromArray(): void
    {
        $data = [
            'ip' => '999.999.999.999',
            'errorCode' => 'INVALID_IP',
            'errorMessage' => 'The provided IP address is not valid.',
        ];

        $error = BatchItemError::fromArray($data);

        $this->assertSame('999.999.999.999', $error->ip);
        $this->assertSame('INVALID_IP', $error->errorCode);
        $this->assertSame('The provided IP address is not valid.', $error->errorMessage);
    }

    // ---------------------------------------------------------------------------
    // BatchResult
    // ---------------------------------------------------------------------------

    public function testBatchResultMixedItems(): void
    {
        $data = [
            'results' => [
                [
                    'ip' => '8.8.8.8',
                    'network' => ['asn' => 'AS15169', 'isp' => 'Google LLC'],
                ],
                [
                    'ip' => 'bad-ip',
                    'errorCode' => 'INVALID_IP',
                    'errorMessage' => 'Not a valid IP.',
                ],
                [
                    'ip' => '1.1.1.1',
                    'network' => ['asn' => 'AS13335', 'isp' => 'Cloudflare'],
                ],
            ],
        ];

        $batch = BatchResult::fromArray($data);

        $this->assertCount(3, $batch->results);
        $this->assertInstanceOf(LookupResult::class, $batch->results[0]);
        $this->assertInstanceOf(BatchItemError::class, $batch->results[1]);
        $this->assertInstanceOf(LookupResult::class, $batch->results[2]);

        /** @var LookupResult $first */
        $first = $batch->results[0];
        $this->assertSame('8.8.8.8', $first->ip);

        /** @var BatchItemError $second */
        $second = $batch->results[1];
        $this->assertSame('INVALID_IP', $second->errorCode);
    }

    public function testBatchResultEmptyResults(): void
    {
        $batch = BatchResult::fromArray(['results' => []]);
        $this->assertSame([], $batch->results);
    }

    // ---------------------------------------------------------------------------
    // MasklenException
    // ---------------------------------------------------------------------------

    public function testMasklenExceptionAccessors(): void
    {
        $exception = new MasklenException(429, 'RATE_LIMITED', 'Too many requests.');

        $this->assertSame(429, $exception->getStatusCode());
        $this->assertSame('RATE_LIMITED', $exception->getErrorCode());
        $this->assertSame('Too many requests.', $exception->getErrorMessage());
    }

    public function testMasklenExceptionIsRuntimeException(): void
    {
        $exception = new MasklenException(401, 'UNAUTHORIZED', 'Invalid API key.');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testMasklenExceptionMessage(): void
    {
        $exception = new MasklenException(404, 'NOT_FOUND', 'IP not found.');
        $this->assertStringContainsString('NOT_FOUND', $exception->getMessage());
        $this->assertStringContainsString('IP not found.', $exception->getMessage());
        $this->assertStringContainsString('404', $exception->getMessage());
    }
}
