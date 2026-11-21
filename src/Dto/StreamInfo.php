<?php

declare(strict_types=1);

namespace Quikcast\Dto;

use Quikcast\Enum\HlsState;

/**
 * An HLS stream. Ingested streams fill generation, playlistRevision and renditionCount;
 * directory streams ($source === 'directory') fill the playlist and listener fields instead.
 */
final readonly class StreamInfo extends Dto
{
    public function __construct(
        public string $name,
        public int $generation,
        public HlsState $state,
        public int $playlistRevision,
        public int $renditionCount,
        public int $retainedBytes,
        public string $source = 'ingest',
        public int $playlistCount = 0,
        public int $activeListeners = 0,
        public int $listenerWindowSeconds = 0,
        public int $activeSessions = 0,
        public int $sessionTimeoutSeconds = 0,
        public int $playlistRequests = 0,
        public int $segmentRequests = 0,
    ) {}

    public function isDirectory(): bool
    {
        return $this->source === 'directory';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::str($data, 'name'),
            self::int($data, 'generation'),
            HlsState::tryFrom(self::str($data, 'state')) ?? HlsState::Active,
            self::int($data, 'playlist_revision'),
            self::int($data, 'rendition_count'),
            self::int($data, 'retained_bytes'),
            self::str($data, 'source', 'ingest'),
            self::int($data, 'playlist_count'),
            self::int($data, 'active_listeners'),
            self::int($data, 'listener_window_seconds'),
            self::int($data, 'active_sessions'),
            self::int($data, 'session_timeout_seconds'),
            self::int($data, 'playlist_requests'),
            self::int($data, 'segment_requests'),
        );
    }
}
