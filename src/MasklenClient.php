<?php

declare(strict_types=1);

namespace Masklen;

use Masklen\Exceptions\MasklenException;
use Masklen\Types\BatchResult;
use Masklen\Types\LookupResult;

/**
 * Client for the masklen.dev IP intelligence API.
 *
 * Requires the curl and json PHP extensions.
 */
class MasklenClient
{
    private const DEFAULT_BASE_URL = 'https://masklen.dev';
    private const TIMEOUT_SECONDS = 30;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = self::DEFAULT_BASE_URL,
    ) {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('The curl PHP extension is required to use MasklenClient.');
        }
        if (!extension_loaded('json')) {
            throw new \RuntimeException('The json PHP extension is required to use MasklenClient.');
        }
    }

    /**
     * Looks up the caller's own IP address.
     *
     * @param string[] $fields Optional subset of fields to return: location, network, privacy, locale
     *
     * @throws MasklenException on API errors
     * @throws \RuntimeException on network or parsing errors
     */
    public function lookupSelf(array $fields = []): LookupResult
    {
        $query = [];
        if ($fields !== []) {
            $query['fields'] = implode(',', $fields);
        }

        $data = $this->request('GET', '/v1/lookup', $query);

        return LookupResult::fromArray($data);
    }

    /**
     * Looks up a specific IPv4 or IPv6 address.
     *
     * @param string   $ip     The IP address to look up
     * @param string[] $fields Optional subset of fields to return: location, network, privacy, locale
     *
     * @throws MasklenException on API errors
     * @throws \RuntimeException on network or parsing errors
     */
    public function lookup(string $ip, array $fields = []): LookupResult
    {
        $query = [];
        if ($fields !== []) {
            $query['fields'] = implode(',', $fields);
        }

        $encodedIp = rawurlencode($ip);
        $data = $this->request('GET', '/v1/lookup/' . $encodedIp, $query);

        return LookupResult::fromArray($data);
    }

    /**
     * Looks up up to 1000 IP addresses in a single request.
     *
     * @param string[] $ips    The IP addresses to look up (maximum 1000)
     * @param string[] $fields Optional subset of fields to return: location, network, privacy, locale
     *
     * @throws MasklenException on API errors
     * @throws \RuntimeException on network or parsing errors
     */
    public function lookupBatch(array $ips, array $fields = []): BatchResult
    {
        $query = [];
        if ($fields !== []) {
            $query['fields'] = implode(',', $fields);
        }

        $body = ['ips' => array_values($ips)];
        $data = $this->request('POST', '/v1/lookup/batch', $query, $body);

        return BatchResult::fromArray($data);
    }

    /**
     * Performs an HTTP request against the API.
     *
     * @param string               $method GET or POST
     * @param string               $path   API path starting with /
     * @param array<string, mixed> $query  Query parameters
     * @param array<string, mixed>|null $body Request body (will be JSON-encoded)
     *
     * @return array<string, mixed> Decoded JSON response
     *
     * @throws MasklenException on non-2xx API responses
     * @throws \RuntimeException on curl errors or invalid JSON
     */
    private function request(
        string $method,
        string $path,
        array $query = [],
        ?array $body = null,
    ): array {
        $url = rtrim($this->baseUrl, '/') . $path;

        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json',
        ];

        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if ($method === 'POST') {
            $jsonBody = json_encode($body, JSON_THROW_ON_ERROR);
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = $jsonBody;
            $curlOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            $curlOptions[CURLOPT_HTTPHEADER][] = 'Content-Length: ' . strlen($jsonBody);
        }

        curl_setopt_array($ch, $curlOptions);

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            throw new \RuntimeException('curl request failed: ' . $curlError);
        }

        if (!is_string($responseBody)) {
            throw new \RuntimeException('Unexpected curl response type.');
        }

        /** @var mixed $decoded */
        $decoded = json_decode($responseBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'Failed to decode API response as JSON: ' . json_last_error_msg()
            );
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('API response was not a JSON object.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new MasklenException(
                statusCode: $statusCode,
                errorCode: (string) ($decoded['errorCode'] ?? 'UNKNOWN_ERROR'),
                errorMessage: (string) ($decoded['errorMessage'] ?? $decoded['message'] ?? 'An unknown error occurred.'),
            );
        }

        return $decoded;
    }
}
