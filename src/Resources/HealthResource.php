<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Quikcast\Exception\TransportException;
use Quikcast\Transport\ManagementTransport;

final class HealthResource
{
    private ClientInterface $http;

    public function __construct(
        private readonly string $baseUrl,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new Client(['http_errors' => false]);
    }

    public function live(): bool
    {
        return $this->probe('/health/live');
    }

    public function ready(): bool
    {
        return $this->probe('/health/ready');
    }

    private function probe(string $path): bool
    {
        try {
            $response = $this->http->request('GET', rtrim($this->baseUrl, '/').$path, [
                'http_errors' => false,
                'headers' => ['User-Agent' => ManagementTransport::USER_AGENT],
            ]);
        } catch (GuzzleException $e) {
            throw new TransportException('HTTP transport failure: '.$e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();

        if ($status === 200) {
            return true;
        }

        if ($status === 503) {
            return false;
        }

        throw new TransportException('Health probe failed with HTTP '.$status);
    }
}
