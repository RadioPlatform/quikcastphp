<?php

declare(strict_types=1);

namespace Quikcast\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Quikcast\Dto\History;
use Quikcast\Dto\ListenerPage;
use Quikcast\Dto\MountInfo;
use Quikcast\Dto\RelaySnapshot;
use Quikcast\Dto\RenditionInfo;
use Quikcast\Dto\ServerInfo;
use Quikcast\Dto\SourceInfo;
use Quikcast\Dto\Stats;
use Quikcast\Dto\StreamInfo;
use Quikcast\Enum\HlsState;
use Quikcast\Enum\RelayState;
use Quikcast\Enum\ServerState;

final class DtoTest extends TestCase
{
    /** @return array<string, mixed> */
    private function fixture(string $name): array
    {
        $json = file_get_contents(__DIR__.'/../Fixtures/'.$name);

        if ($json === false) {
            self::fail('Missing fixture '.$name);
        }

        $data = json_decode($json, true);

        if (! is_array($data)) {
            self::fail('Invalid fixture '.$name);
        }

        $out = [];

        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /** @return list<array<string, mixed>> */
    private function fixtureList(string $name): array
    {
        $json = file_get_contents(__DIR__.'/../Fixtures/'.$name);

        if ($json === false) {
            self::fail('Missing fixture '.$name);
        }

        $data = json_decode($json, true);

        if (! is_array($data)) {
            self::fail('Invalid fixture '.$name);
        }

        $out = [];

        foreach ($data as $item) {
            if (! is_array($item)) {
                self::fail('Invalid fixture '.$name);
            }

            $row = [];

            foreach ($item as $key => $value) {
                if (is_string($key)) {
                    $row[$key] = $value;
                }
            }

            $out[] = $row;
        }

        return $out;
    }

    public function test_server_info_maps(): void
    {
        $server = ServerInfo::fromArray($this->fixture('server.json'));

        $this->assertSame('Quikcast', $server->name);
        $this->assertSame('0.1.0', $server->version);
        $this->assertSame(ServerState::Accepting, $server->state);
        $this->assertNull($server->drainStartedAt);
        $this->assertSame(256, $server->limits->maxConnections);
        $this->assertFalse($server->isDraining());
    }

    public function test_draining_server_maps(): void
    {
        $server = ServerInfo::fromArray($this->fixture('server-draining.json'));

        $this->assertTrue($server->isDraining());
        $this->assertSame(1791460860000, $server->drainStartedAt);
        $this->assertSame(0, $server->drainAgeMs);
    }

    public function test_stats_map(): void
    {
        $stats = Stats::fromArray($this->fixture('stats.json'));

        $this->assertSame(2, $stats->configuredMounts);
        $this->assertSame(1, $stats->activeSources);
        $this->assertSame(2097152, $stats->relays->allocationReservationBytes);
        $this->assertSame(536870912, $stats->hls->allocationBudget);
    }

    public function test_mount_with_source_maps(): void
    {
        $mount = MountInfo::fromArray($this->fixture('mount.json'));

        $this->assertSame('/radio.mp3', $mount->path);
        $this->assertTrue($mount->hasSource());
        $this->assertNotNull($mount->source);
        $this->assertSame(7, $mount->source->generation);
        $this->assertSame('Example Artist - Example Track', $mount->source->track->title);
        $this->assertSame(128, $mount->source->sourceMetadata->declaredBitrateKbps);
        $this->assertSame(64, $mount->listenerLimit);
    }

    public function test_mount_without_source_maps(): void
    {
        $mount = MountInfo::fromArray($this->fixture('mount-no-source.json'));

        $this->assertNull($mount->source);
        $this->assertFalse($mount->hasSource());
        $this->assertNull($mount->listenerLimit);
    }

    public function test_source_maps(): void
    {
        $source = SourceInfo::fromArray($this->fixture('source.json'));

        $this->assertSame('mp3', $source->format);
        $this->assertSame('mp3', $source->codec);
        $this->assertSame('127.0.0.1', $source->connection->clientIp);
    }

    public function test_listener_page_maps(): void
    {
        $page = ListenerPage::fromArray($this->fixture('listeners-page.json'));

        $this->assertCount(1, $page->items);
        $this->assertTrue($page->hasMore());
        $this->assertSame('0123456789abcdef0123456789abcdef', $page->nextAfter);
    }

    public function test_empty_listener_page_has_no_more(): void
    {
        $page = ListenerPage::fromArray(['items' => [], 'next_after' => null]);

        $this->assertSame([], $page->items);
        $this->assertFalse($page->hasMore());
    }

    public function test_stream_and_rendition_map(): void
    {
        $streams = array_map(StreamInfo::fromArray(...), $this->fixtureList('hls-streams.json'));
        $rendition = RenditionInfo::fromArray($this->fixture('rendition.json'));

        $this->assertCount(1, $streams);
        $this->assertSame(HlsState::Active, $streams[0]->state);
        $this->assertSame('mpeg-ts', $rendition->format);
        $this->assertSame(10, $rendition->mediaSequence);
        $this->assertSame([], $rendition->audioProperties);
    }

    public function test_relay_maps(): void
    {
        $relay = RelaySnapshot::fromArray($this->fixture('relay.json'));

        $this->assertSame('/remote.mp3', $relay->mount);
        $this->assertSame(RelayState::Connected, $relay->state);
        $this->assertSame(4, $relay->revision);
        $this->assertTrue($relay->desiredRunning);
    }

    public function test_history_parses_minutes_and_hours(): void
    {
        $history = History::fromArray($this->fixture('history.json'));

        $this->assertNull($history->mount);
        $this->assertSame(1440, $history->retentionMinutes);
        $this->assertSame(41, $history->peakListeners24h);
        $this->assertSame(207, $history->uniqueListenersEstimate24h);
        $this->assertSame(12, $history->minutes[0]->peakListeners);
        $this->assertSame(88, $history->hours[0]->uniqueListenersEstimate);
    }

    public function test_mount_exposes_policies_counters_and_ring(): void
    {
        $mount = MountInfo::fromArray($this->fixture('mount-full.json'));

        $this->assertSame('/backup.mp3', $mount->fallback);
        $this->assertSame(['US'], $mount->access?->countriesAllow);
        $this->assertSame(6, $mount->sourceAccess?->ipDenyRules);
        $this->assertTrue($mount->listenerAuth?->isSigned());
        $this->assertSame(3600, $mount->listenerAuth->maxTtlSeconds);
        $this->assertSame(127, $mount->remainingListenerCapacity);
        $this->assertSame(['client_closed' => 4], $mount->listenerDisconnectsTotal);
        $this->assertSame('administrative_disconnect', $mount->lastSourceDisconnectReason);
        $this->assertSame(13, $mount->source?->ring?->latestSequence);
        $this->assertSame('2026-10-25T10:00:01Z', $mount->source->connectedSince);
    }
}
