<?php

declare(strict_types=1);

namespace Quikcast;

use GuzzleHttp\ClientInterface;
use Quikcast\Resources\HealthResource;
use Quikcast\Resources\HistoryResource;
use Quikcast\Resources\HlsResource;
use Quikcast\Resources\ListenersResource;
use Quikcast\Resources\MetadataResource;
use Quikcast\Resources\MetricsResource;
use Quikcast\Resources\MountsResource;
use Quikcast\Resources\RelaysResource;
use Quikcast\Resources\ServerResource;
use Quikcast\Resources\SourcesResource;
use Quikcast\Resources\StatsResource;
use Quikcast\Transport\ManagementTransport;

final class QuikcastClient
{
    private readonly ManagementTransport $transport;

    private ?ServerResource $server = null;

    private ?StatsResource $stats = null;

    private ?MountsResource $mounts = null;

    private ?SourcesResource $sources = null;

    private ?ListenersResource $listeners = null;

    private ?HlsResource $hls = null;

    private ?RelaysResource $relays = null;

    private ?MetadataResource $metadata = null;

    private ?HealthResource $health = null;

    private ?HistoryResource $history = null;

    private ?MetricsResource $metrics = null;

    public function __construct(
        private readonly Config $config,
        ?ClientInterface $http = null,
        private readonly ?ClientInterface $sourceHttp = null,
    ) {
        $this->transport = new ManagementTransport($config, $http);
    }

    public static function create(string $baseUrl, string $token, ?ClientInterface $http = null): self
    {
        return new self(new Config($baseUrl, $token), $http);
    }

    public function server(): ServerResource
    {
        return $this->server ??= new ServerResource($this->transport);
    }

    public function stats(): StatsResource
    {
        return $this->stats ??= new StatsResource($this->transport);
    }

    public function mounts(): MountsResource
    {
        return $this->mounts ??= new MountsResource($this->transport);
    }

    public function sources(): SourcesResource
    {
        return $this->sources ??= new SourcesResource($this->transport);
    }

    public function listeners(): ListenersResource
    {
        return $this->listeners ??= new ListenersResource($this->transport);
    }

    public function hls(): HlsResource
    {
        return $this->hls ??= new HlsResource($this->transport);
    }

    public function relays(): RelaysResource
    {
        return $this->relays ??= new RelaysResource($this->transport);
    }

    public function metrics(): MetricsResource
    {
        return $this->metrics ??= new MetricsResource($this->config->baseUrl());
    }

    public function history(): HistoryResource
    {
        return $this->history ??= new HistoryResource($this->transport);
    }

    public function metadata(): MetadataResource
    {
        return $this->metadata ??= new MetadataResource($this->sourceHttp);
    }

    public function health(): HealthResource
    {
        return $this->health ??= new HealthResource($this->config->baseUrl());
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
