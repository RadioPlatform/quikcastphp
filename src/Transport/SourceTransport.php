<?php

declare(strict_types=1);

namespace Quikcast\Transport;

use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\RequestException;
use Quikcast\Enum\ErrorCode;
use Quikcast\Exception\TransportException;
use Quikcast\Exception\UnauthorizedException;
use Quikcast\Support\MountPath;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Transport for the source metadata operation.
 *
 * This is NOT part of the Bearer management API: it runs on the streaming
 * listener, uses the mount's source credentials via HTTP Basic auth, and
 * returns an ICY XML response.
 */
final class SourceTransport
{
    private HttpClient $http;

    public function __construct(?HttpClientInterface $http = null)
    {
        $this->http = new HttpClient($http);
    }

    public function updateSong(
        string $listenBaseUrl,
        string $mount,
        string $sourceUser,
        string $sourceSecret,
        string $song,
    ): void {
        $this->update($listenBaseUrl, $mount, $sourceUser, $sourceSecret, ['song' => $song]);
    }

    /**
     * Update the track using separate artist and title fields ("artist - title").
     */
    public function updateTrack(
        string $listenBaseUrl,
        string $mount,
        string $sourceUser,
        string $sourceSecret,
        string $title,
        ?string $artist = null,
    ): void {
        $fields = ['title' => $title];
        if ($artist !== null) {
            $fields['artist'] = $artist;
        }

        $this->update($listenBaseUrl, $mount, $sourceUser, $sourceSecret, $fields);
    }

    /**
     * @param  array<string, string>  $fields
     */
    private function update(
        string $listenBaseUrl,
        string $mount,
        string $sourceUser,
        string $sourceSecret,
        array $fields,
    ): void {
        $canonical = MountPath::canonicalize($mount);
        $url = rtrim($listenBaseUrl, '/').'/api/metadata?'.http_build_query(
            ['mount' => $canonical, 'mode' => 'updinfo', 'charset' => 'UTF-8'] + $fields,
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        try {
            $status = $this->http
                ->withBasicAuth($sourceUser, $sourceSecret)
                ->withUserAgent(ManagementTransport::USER_AGENT)
                ->get($url)
                ->status();
        } catch (RequestException $e) {
            throw new TransportException('HTTP transport failure: '.$e->getMessage(), 0, $e);
        }

        if ($status === 401) {
            throw new UnauthorizedException('Source authentication failed', 401, ErrorCode::Unauthorized);
        }

        if ($status < 200 || $status >= 300) {
            throw new TransportException('Metadata update failed with HTTP '.$status);
        }
    }
}
