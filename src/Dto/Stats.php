<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class Stats extends Dto
{
    /**
     * @param  array<string, int>  $sourceDisconnectsTotal  keyed by disconnect reason
     * @param  array<string, int>  $listenerDisconnectsTotal  keyed by disconnect reason
     */
    public function __construct(
        public int $uptimeMs,
        public int $configuredMounts,
        public int $activeMounts,
        public int $activeSources,
        public int $activeListeners,
        public int $activeConnections,
        public int $activeHandshakes,
        public int $sourceConnectionsTotal,
        public int $listenerConnectionsTotal,
        public int $bytesIngestedTotal,
        public int $bytesServedTotal,
        public int $slowConsumerDisconnects,
        public int $authenticationFailures,
        public int $rejectedConnections,
        public int $drainRejections,
        public int $hlsRequests,
        public int $hlsSegmentsServed,
        public int $hlsPublicationConflicts,
        public int $hlsActiveUploads,
        public RelaySummary $relays,
        public HlsSummary $hls,
        public int $fallbackListenerAdmissionsTotal = 0,
        public ?string $startedSince = null,
        public string $state = 'accepting',
        public array $sourceDisconnectsTotal = [],
        public array $listenerDisconnectsTotal = [],
        public int $sourceAuthDenialsTotal = 0,
        public int $sourceAclDenialsTotal = 0,
        public int $sourceConflictsTotal = 0,
        public int $listenerAclDenialsTotal = 0,
        public int $listenerSignedAuthDenialsTotal = 0,
        public int $hlsSignedAuthDenialsTotal = 0,
        public int $listenerBodyBytesSentTotal = 0,
        public int $listenerMediaBytesSentTotal = 0,
        public int $listenerIcyBytesSentTotal = 0,
        public int $hlsIngestBytesTotal = 0,
        public int $hlsBodyBytesSentTotal = 0,
        public int $hlsInitializationsAcceptedTotal = 0,
        public int $hlsSegmentsAcceptedTotal = 0,
        public int $hlsPublicationsTotal = 0,
        public int $hlsRejectionsTotal = 0,
        public int $hlsGetResponsesTotal = 0,
        public int $hlsHeadResponsesTotal = 0,
        public int $hlsPartialResponsesTotal = 0,
        public int $hlsNotModifiedResponsesTotal = 0,
        public int $hlsRangeRejectionsTotal = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'uptime_ms'),
            self::int($data, 'configured_mounts'),
            self::int($data, 'active_mounts'),
            self::int($data, 'active_sources'),
            self::int($data, 'active_listeners'),
            self::int($data, 'active_connections'),
            self::int($data, 'active_handshakes'),
            self::int($data, 'source_connections_total'),
            self::int($data, 'listener_connections_total'),
            self::int($data, 'bytes_ingested_total'),
            self::int($data, 'bytes_served_total'),
            self::int($data, 'slow_consumer_disconnects'),
            self::int($data, 'authentication_failures'),
            self::int($data, 'rejected_connections'),
            self::int($data, 'drain_rejections'),
            self::int($data, 'hls_requests'),
            self::int($data, 'hls_segments_served'),
            self::int($data, 'hls_publication_conflicts'),
            self::int($data, 'hls_active_uploads'),
            RelaySummary::fromArray(self::arr($data, 'relays')),
            HlsSummary::fromArray(self::arr($data, 'hls')),
            self::int($data, 'fallback_listener_admissions_total'),
            self::nullableStr($data, 'started_since'),
            self::str($data, 'state', 'accepting'),
            self::intMap($data, 'source_disconnects_total'),
            self::intMap($data, 'listener_disconnects_total'),
            self::int($data, 'source_auth_denials_total'),
            self::int($data, 'source_acl_denials_total'),
            self::int($data, 'source_conflicts_total'),
            self::int($data, 'listener_acl_denials_total'),
            self::int($data, 'listener_signed_auth_denials_total'),
            self::int($data, 'hls_signed_auth_denials_total'),
            self::int($data, 'listener_body_bytes_sent_total'),
            self::int($data, 'listener_media_bytes_sent_total'),
            self::int($data, 'listener_icy_bytes_sent_total'),
            self::int($data, 'hls_ingest_bytes_total'),
            self::int($data, 'hls_body_bytes_sent_total'),
            self::int($data, 'hls_initializations_accepted_total'),
            self::int($data, 'hls_segments_accepted_total'),
            self::int($data, 'hls_publications_total'),
            self::int($data, 'hls_rejections_total'),
            self::int($data, 'hls_get_responses_total'),
            self::int($data, 'hls_head_responses_total'),
            self::int($data, 'hls_partial_responses_total'),
            self::int($data, 'hls_not_modified_responses_total'),
            self::int($data, 'hls_range_rejections_total'),
        );
    }
}
