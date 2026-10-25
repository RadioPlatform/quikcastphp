<?php

declare(strict_types=1);

namespace Quikcast\Dto;

use Quikcast\Enum\MountState;

final readonly class MountInfo extends Dto
{
    /**
     * @param  array<string, int>  $sourceDisconnectsTotal  keyed by disconnect reason
     * @param  array<string, int>  $listenerDisconnectsTotal  keyed by disconnect reason
     */
    public function __construct(
        public string $path,
        public ?SourceInfo $source,
        public int $activeListeners,
        public ?int $listenerLimit,
        public int $bytesIngested,
        public int $bytesServed,
        public MountState $state,
        public bool $dynamic = false,
        public ?string $fallback = null,
        public ?AccessPolicy $access = null,
        public ?SourceAccessPolicy $sourceAccess = null,
        public ?ListenerAuth $listenerAuth = null,
        public ?int $effectiveListenerLimit = null,
        public ?int $remainingListenerCapacity = null,
        public int $sourceGenerationsStartedTotal = 0,
        public int $listenersAcceptedTotal = 0,
        public int $listenersAcceptedCurrentGeneration = 0,
        public ?string $lastSourceDisconnectReason = null,
        public array $sourceDisconnectsTotal = [],
        public array $listenerDisconnectsTotal = [],
        public int $slowConsumerDisconnectsTotal = 0,
        public ?RelaySnapshot $relay = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rawSource = $data['source'] ?? null;
        $source = is_array($rawSource) ? SourceInfo::fromArray(self::map($rawSource)) : null;

        return new self(
            path: self::str($data, 'path'),
            source: $source,
            activeListeners: self::int($data, 'active_listeners'),
            listenerLimit: self::nullableInt($data, 'listener_limit'),
            bytesIngested: self::int($data, 'bytes_ingested'),
            bytesServed: self::int($data, 'bytes_served'),
            state: MountState::tryFrom(self::str($data, 'state')) ?? MountState::Accepting,
            dynamic: self::bool($data, 'dynamic'),
            fallback: self::nullableStr($data, 'fallback'),
            access: isset($data['access']) ? AccessPolicy::fromArray(self::arr($data, 'access')) : null,
            sourceAccess: isset($data['source_access']) ? SourceAccessPolicy::fromArray(self::arr($data, 'source_access')) : null,
            listenerAuth: isset($data['listener_auth']) ? ListenerAuth::fromArray(self::arr($data, 'listener_auth')) : null,
            effectiveListenerLimit: self::nullableInt($data, 'effective_listener_limit'),
            remainingListenerCapacity: self::nullableInt($data, 'remaining_listener_capacity'),
            sourceGenerationsStartedTotal: self::int($data, 'source_generations_started_total'),
            listenersAcceptedTotal: self::int($data, 'listeners_accepted_total'),
            listenersAcceptedCurrentGeneration: self::int($data, 'listeners_accepted_current_generation'),
            lastSourceDisconnectReason: self::nullableStr($data, 'last_source_disconnect_reason'),
            sourceDisconnectsTotal: self::intMap($data, 'source_disconnects_total'),
            listenerDisconnectsTotal: self::intMap($data, 'listener_disconnects_total'),
            slowConsumerDisconnectsTotal: self::int($data, 'slow_consumer_disconnects_total'),
            relay: is_array($data['relay'] ?? null) ? RelaySnapshot::fromArray(self::map($data['relay'])) : null,
        );
    }

    public function hasSource(): bool
    {
        return $this->source !== null;
    }
}
