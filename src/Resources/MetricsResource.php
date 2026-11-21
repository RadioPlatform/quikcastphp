<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\RequestException;
use Quikcast\Exception\TransportException;
use Quikcast\Transport\ManagementTransport;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Prometheus text exposition from the admin listener's /metrics (no token required).
 */
final class MetricsResource
{
    private HttpClient $http;

    public function __construct(
        private readonly string $baseUrl,
        ?HttpClientInterface $http = null,
    ) {
        $this->http = new HttpClient($http);
    }

    public function text(): string
    {
        try {
            $response = $this->http
                ->withUserAgent(ManagementTransport::USER_AGENT)
                ->get(rtrim($this->baseUrl, '/').'/metrics');

            $status = $response->status();

            if ($status !== 200) {
                throw new TransportException('Metrics request failed with HTTP '.$status);
            }

            return $response->body();
        } catch (RequestException $e) {
            throw new TransportException('HTTP transport failure: '.$e->getMessage(), 0, $e);
        }
    }
}
