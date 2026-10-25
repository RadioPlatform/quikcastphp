<?php

declare(strict_types=1);

namespace Quikcast\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Quikcast\Enum\ErrorCode;
use Quikcast\Exception\TransportException;
use Quikcast\Exception\UnauthorizedException;
use Quikcast\Support\MountPath;

/**
 * Transport for the source metadata operation.
 *
 * This is NOT part of the Bearer management API: it runs on the streaming
 * listener, uses the mount's source credentials via HTTP Basic auth, and
 * returns an ICY XML response.
 */
final class SourceTransport
{
    private ClientInterface $http;

    public function __construct(?ClientInterface $http = null)
    {
        $this->http = $http ?? new Client(['http_errors' => false]);
    }

    public function updateSong(
        string $listenBaseUrl,
        string $mount,
        string $sourceUser,
        string $sourceSecret,
        string $song,
    ): void {
        $canonical = MountPath::canonicalize($mount);
        $url = rtrim($listenBaseUrl, '/').$canonical
            .'?'.http_build_query(
                ['mode' => 'updinfo', 'song' => $song],
                '',
                '&',
                PHP_QUERY_RFC3986
            );

        try {
            $response = $this->http->request('GET', $url, [
                'http_errors' => false,
                'auth' => [$sourceUser, $sourceSecret],
                'headers' => ['User-Agent' => ManagementTransport::USER_AGENT],
            ]);
        } catch (GuzzleException $e) {
            throw new TransportException('HTTP transport failure: '.$e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();

        if ($status === 401) {
            throw new UnauthorizedException('Source authentication failed', 401, ErrorCode::Unauthorized);
        }

        if ($status < 200 || $status >= 300) {
            throw new TransportException('Metadata update failed with HTTP '.$status);
        }
    }
}
