<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\RequestException;
use Quikcast\Exception\TransportException;
use Quikcast\Transport\ManagementTransport;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HealthResource
{
    private HttpClient $http;

    public function __construct(
        private readonly string $baseUrl,
        ?HttpClientInterface $http = null,
    ) {
        $this->http = new HttpClient($http);
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
            $status = $this->http
                ->withUserAgent(ManagementTransport::USER_AGENT)
                ->get(rtrim($this->baseUrl, '/').$path)
                ->status();
        } catch (RequestException $e) {
            throw new TransportException('HTTP transport failure: '.$e->getMessage(), 0, $e);
        }

        if ($status === 200) {
            return true;
        }

        if ($status === 503) {
            return false;
        }

        throw new TransportException('Health probe failed with HTTP '.$status);
    }
}
