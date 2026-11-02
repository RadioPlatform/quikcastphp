<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use GuzzleHttp\ClientInterface;
use Quikcast\Transport\SourceTransport;

final readonly class MetadataResource
{
    private SourceTransport $transport;

    public function __construct(?ClientInterface $http = null)
    {
        $this->transport = new SourceTransport($http);
    }

    /**
     * Update the current song for a mount.
     *
     * This hits the streaming listener (not the management API) using the
     * mount's source credentials via HTTP Basic auth.
     */
    public function updateSong(
        string $listenBaseUrl,
        string $mount,
        string $sourceUser,
        string $sourceSecret,
        string $song,
    ): void {
        $this->transport->updateSong($listenBaseUrl, $mount, $sourceUser, $sourceSecret, $song);
    }

    /**
     * Update the track from separate artist and title fields.
     */
    public function updateTrack(
        string $listenBaseUrl,
        string $mount,
        string $sourceUser,
        string $sourceSecret,
        string $title,
        ?string $artist = null,
    ): void {
        $this->transport->updateTrack($listenBaseUrl, $mount, $sourceUser, $sourceSecret, $title, $artist);
    }
}
