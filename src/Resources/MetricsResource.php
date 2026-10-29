<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Quikcast\Exception\TransportException;
use Quikcast\Transport\ManagementTransport;

/**
 * Prometheus text exposition from the admin listener's /metrics (no token required).
 */
final class MetricsResource
{
    private ClientInterface $http;

    public function __construct(
        private readonly string $baseUrl,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new Client(['http_errors' => false]);
    }

    public function text(): string
    {
        try {
            $response = $this->http->request('GET', rtrim($this->baseUrl, '/').'/metrics', [
                'http_errors' => false,
                'headers' => ['User-Agent' => ManagementTransport::USER_AGENT],
            ]);
        } catch (GuzzleException $e) {
            throw new TransportException('HTTP transport failure: '.$e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new TransportException('Metrics request failed with HTTP '.$response->getStatusCode());
        }

        return (string) $response->getBody();
    }
}
