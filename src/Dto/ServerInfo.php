<?php

declare(strict_types=1);

namespace Quikcast\Dto;

use Quikcast\Enum\ServerState;

final readonly class ServerInfo extends Dto
{
    /**
     * @param  list<string>  $capabilities
     * @param  array<string, ListenerAuth>  $hlsListenerAuth  keyed by HLS stream path
     */
    public function __construct(
        public string $name,
        public string $version,
        public int $startedAt,
        public int $uptimeMs,
        public int $configGeneration,
        public int $configCommittedAt,
        public ServerState $state,
        public ?int $drainStartedAt,
        public ?int $drainAgeMs,
        public array $capabilities,
        public ServerLimits $limits,
        public ?AccessPolicy $access = null,
        public bool $geoipLoaded = false,
        public bool $mountAccessEnabled = false,
        public array $hlsListenerAuth = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: self::str($data, 'name', 'Quikcast'),
            version: self::str($data, 'version'),
            startedAt: self::int($data, 'started_at'),
            uptimeMs: self::int($data, 'uptime_ms'),
            configGeneration: self::int($data, 'config_generation'),
            configCommittedAt: self::int($data, 'config_committed_at'),
            state: ServerState::tryFrom(self::str($data, 'state')) ?? ServerState::Accepting,
            drainStartedAt: self::nullableInt($data, 'drain_started_at'),
            drainAgeMs: self::nullableInt($data, 'drain_age_ms'),
            capabilities: self::strList($data, 'capabilities'),
            limits: ServerLimits::fromArray(self::arr($data, 'limits')),
            access: isset($data['access']) ? AccessPolicy::fromArray(self::arr($data, 'access')) : null,
            geoipLoaded: self::bool($data, 'geoip_loaded'),
            mountAccessEnabled: self::bool($data, 'mount_access_enabled'),
            hlsListenerAuth: array_map(
                fn (mixed $info): ListenerAuth => ListenerAuth::fromArray(self::map($info)),
                self::arr($data, 'hls_listener_auth'),
            ),
        );
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }

    public function isDraining(): bool
    {
        return $this->state === ServerState::Draining;
    }
}
