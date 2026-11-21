<?php

declare(strict_types=1);

namespace Quikcast\Tests\Feature;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Quikcast\Dto\Stats;
use Quikcast\Enum\HlsState;
use Quikcast\Enum\ServerState;
use Quikcast\Exception\BanListFullException;
use Quikcast\Exception\BanNotFoundException;
use Quikcast\Exception\ListenerNotFoundException;
use Quikcast\Exception\MountNotFoundException;
use Quikcast\Exception\RevocationNotFoundException;
use Quikcast\QuikcastClient;
use Quikcast\Resources\MetricsResource;
use Quikcast\Tests\Support\FakeHttp;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ResourcesTest extends TestCase
{
    private FakeHttp $http;

    /** @param list<MockResponse> $responses */
    private function client(array $responses): QuikcastClient
    {
        $this->http = new FakeHttp($responses);

        return QuikcastClient::create('http://127.0.0.1:9090', 'test-token', $this->http->client);
    }

    private function fixtureBody(string $name): string
    {
        $body = file_get_contents(__DIR__.'/../Fixtures/'.$name);

        if ($body === false) {
            self::fail('Missing fixture '.$name);
        }

        return $body;
    }

    public function test_get_server(): void
    {
        $client = $this->client([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('server.json')),
        ]);

        $server = $client->server()->get();

        $this->assertSame(ServerState::Accepting, $server->state);
        $this->assertSame('/api/server', $this->http->last()->path());
    }

    public function test_drain_server_posts(): void
    {
        $client = $this->client([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('server-draining.json')),
        ]);

        $server = $client->server()->drain();

        $this->assertTrue($server->isDraining());
        $this->assertSame('POST', $this->http->last()->method);
    }

    public function test_list_mounts(): void
    {
        $client = $this->client([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], '['.$this->fixtureBody('mount.json').']'),
        ]);

        $mounts = $client->mounts()->list();

        $this->assertCount(1, $mounts);
        $this->assertSame('/radio.mp3', $mounts[0]->path);
    }

    public function test_get_mount_encodes_nested_path(): void
    {
        $client = $this->client([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('mount.json')),
        ]);

        $client->mounts()->get('nested/radio.mp3');

        $this->assertSame('/api/mounts/nested/radio.mp3', $this->http->last()->path());
    }

    public function test_get_source_returns_null_for_disconnected(): void
    {
        $client = $this->client([FakeHttp::response(200, ['Content-Type' => 'application/json'], 'null')]);

        $this->assertNull($client->sources()->get('/radio.mp3'));
    }

    public function test_disconnect_source_without_generation(): void
    {
        $client = $this->client([FakeHttp::response(204)]);

        $client->sources()->disconnect('/radio.mp3');

        $request = $this->http->last();
        $this->assertSame('DELETE', $request->method);
        $this->assertSame('', $request->query());
    }

    public function test_listeners_page_validates_limit_client_side(): void
    {
        $client = $this->client([]);

        $this->expectException(InvalidArgumentException::class);
        $client->listeners()->page('/radio.mp3', 201);
    }

    public function test_listeners_page_validates_cursor_client_side(): void
    {
        $client = $this->client([]);

        $this->expectException(InvalidArgumentException::class);
        $client->listeners()->page('/radio.mp3', 10, 'not-a-cursor');
    }

    public function test_listeners_paginate_walks_cursor(): void
    {
        $client = $this->client([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('listeners-page.json')),
            FakeHttp::response(200, ['Content-Type' => 'application/json'], '{"items":[],"next_after":null}'),
        ]);

        $all = $client->listeners()->all('/radio.mp3', 1);

        $this->assertCount(1, $all);
        $this->assertCount(2, $this->http->requests);
        $secondQuery = [];
        parse_str($this->http->last(1)->query(), $secondQuery);
        $this->assertSame('0123456789abcdef0123456789abcdef', $secondQuery['after'] ?? null);
    }

    public function test_disconnect_listener_validates_id(): void
    {
        $client = $this->client([]);

        $this->expectException(InvalidArgumentException::class);
        $client->listeners()->disconnect('bogus');
    }

    public function test_listener_not_found_maps(): void
    {
        $client = $this->client([
            FakeHttp::response(404, [], '{"error":{"code":"listener_not_found","message":"Listener not found"}}'),
        ]);

        $this->expectException(ListenerNotFoundException::class);
        $client->listeners()->get('0123456789abcdef0123456789abcdef');
    }

    public function test_hls_lists_and_gets(): void
    {
        $client = $this->client([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('hls-streams.json')),
            FakeHttp::response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('rendition.json')),
        ]);

        $streams = $client->hls()->listStreams();
        $rendition = $client->hls()->getRendition('radio', 'audio');

        $this->assertCount(1, $streams);
        $this->assertSame('audio', $rendition->name);
        $this->assertSame('/api/hls/radio/renditions/audio', $this->http->last(1)->path());
    }

    public function test_relay_restart_posts_with_revision(): void
    {
        $client = $this->client([
            FakeHttp::response(202, ['Content-Type' => 'application/json'], $this->fixtureBody('relay.json')),
        ]);

        $snapshot = $client->relays()->restart('/remote.mp3', 4);

        $this->assertSame(4, $snapshot->revision);
        $request = $this->http->last();
        $this->assertSame('POST', $request->method);
        $this->assertStringContainsString('revision=4', $request->query());
    }

    public function test_unknown_mount_throws(): void
    {
        $client = $this->client([
            FakeHttp::response(404, [], '{"error":{"code":"mount_not_found","message":"Mount not found"}}'),
        ]);

        $this->expectException(MountNotFoundException::class);
        $client->mounts()->get('/missing.mp3');
    }

    public function test_history_sends_canonical_mount_and_minutes(): void
    {
        $client = $this->client([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('history.json')),
        ]);

        $history = $client->history()->get('nested/radio.mp3', 5);

        $this->assertSame(41, $history->peakListeners24h);
        $this->assertSame('/api/history', $this->http->last()->path());
        $this->assertSame('mount=%2Fnested%2Fradio.mp3&minutes=5', $this->http->last()->query());
    }

    public function test_history_validates_minutes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client([])->history()->get(null, 1441);
    }

    public function test_metrics_returns_prometheus_text(): void
    {
        $http = new FakeHttp([FakeHttp::response(200, [], "# TYPE ready gauge\nready 1\n")]);

        $text = (new MetricsResource('http://127.0.0.1:9090/', $http->client))->text();

        $this->assertStringContainsString('ready 1', $text);
    }

    public function test_bans_list(): void
    {
        $client = $this->client([
            FakeHttp::response(200, [], '{"items":[{"ip":"203.0.113.0/24","expires_in_seconds":3590,"reason":"scraper"},{"ip":"198.51.100.7","expires_in_seconds":60,"reason":null}]}'),
        ]);

        $bans = $client->bans()->list();

        $this->assertCount(2, $bans);
        $this->assertSame('203.0.113.0/24', $bans[0]->ip);
        $this->assertSame('scraper', $bans[0]->reason);
        $this->assertNull($bans[1]->reason);
        $this->assertSame('/api/bans', $this->http->last()->path());
    }

    public function test_ban_create_sends_query_and_returns_result(): void
    {
        $client = $this->client([
            FakeHttp::response(201, [], '{"ip":"203.0.113.0/24","expires_in_seconds":86400,"kicked_listeners":3}'),
        ]);

        $result = $client->bans()->create('203.0.113.0/24', 86400, 'scraper', kick: true);

        $this->assertSame(3, $result->kickedListeners);
        $request = $this->http->last();
        $this->assertSame('POST', $request->method);
        $this->assertSame('/api/bans', $request->path());
        parse_str($request->query(), $query);
        $this->assertSame(
            ['ip' => '203.0.113.0/24', 'ttl_seconds' => '86400', 'reason' => 'scraper', 'kick' => 'true'],
            $query
        );
    }

    public function test_ban_create_minimal_sends_only_ip(): void
    {
        $client = $this->client([FakeHttp::response(201, [], '{"ip":"198.51.100.7","expires_in_seconds":3600,"kicked_listeners":0}')]);

        $client->bans()->create('198.51.100.7');

        $this->assertSame('ip=198.51.100.7', $this->http->last()->query());
    }

    public function test_ban_create_validates_client_side(): void
    {
        $client = $this->client([]);

        $this->expectException(InvalidArgumentException::class);
        $client->bans()->create('198.51.100.7', 0);
    }

    public function test_ban_lift_and_not_found(): void
    {
        $client = $this->client([
            FakeHttp::response(204),
            FakeHttp::response(404, [], '{"error":{"code":"ban_not_found","message":"Ban not found"}}'),
        ]);

        $client->bans()->lift('203.0.113.0/24');
        $this->assertSame('DELETE', $this->http->last()->method);
        $this->assertSame('ip=203.0.113.0%2F24', $this->http->last()->query());

        $this->expectException(BanNotFoundException::class);
        $client->bans()->lift('10.0.0.1');
    }

    public function test_ban_list_full_maps(): void
    {
        $client = $this->client([
            FakeHttp::response(507, [], '{"error":{"code":"ban_list_full","message":"Ban list full"}}'),
        ]);

        $this->expectException(BanListFullException::class);
        $client->bans()->create('198.51.100.7');
    }

    public function test_revocations_revoke_list_and_restore(): void
    {
        $client = $this->client([
            FakeHttp::response(201, [], '{"items":[{"signature":"abc123","expires_in_seconds":3000}]}'),
            FakeHttp::response(200, [], '{"items":[]}'),
            FakeHttp::response(204),
            FakeHttp::response(404, [], '{"error":{"code":"revocation_not_found","message":"Revocation not found"}}'),
        ]);

        $revoked = $client->revocations()->revoke('abc123', 1790003000);
        $this->assertSame('abc123', $revoked[0]->signature);
        $this->assertSame('POST', $this->http->last()->method);
        $this->assertSame('sig=abc123&expires=1790003000', $this->http->last()->query());

        $this->assertSame([], $client->revocations()->list());

        $client->revocations()->restore('abc123');
        $this->assertSame('DELETE', $this->http->last()->method);
        $this->assertSame('sig=abc123', $this->http->last()->query());

        $this->expectException(RevocationNotFoundException::class);
        $client->revocations()->restore('missing');
    }

    public function test_hls_directory_stream_and_sessions(): void
    {
        $client = $this->client([
            FakeHttp::response(200, [], '[{"name":"radio","source":"directory","state":"empty","playlist_count":2,"retained_bytes":481920,"active_listeners":12,"listener_window_seconds":60,"active_sessions":14,"session_timeout_seconds":30,"playlist_requests":5231,"segment_requests":5198}]'),
            FakeHttp::response(200, [], '[{"id":"9f3a1c5e7b2d4086","ip":"203.0.113.8","user_agent":null,"country":"US","first_seen":1767225600,"last_seen":1767225690,"duration_seconds":90,"playlist_requests":45,"segment_requests":44,"bytes_served":1843200}]'),
        ]);

        $stream = $client->hls()->listStreams()[0];
        $this->assertTrue($stream->isDirectory());
        $this->assertSame(HlsState::Empty, $stream->state);
        $this->assertSame(12, $stream->activeListeners);
        $this->assertSame(14, $stream->activeSessions);
        $this->assertSame(5198, $stream->segmentRequests);

        $sessions = $client->hls()->listeners('radio');
        $this->assertSame('/api/hls/radio/listeners', $this->http->last()->path());
        $this->assertSame('US', $sessions[0]->country);
        $this->assertNull($sessions[0]->userAgent);
        $this->assertSame(1843200, $sessions[0]->bytesServed);
    }

    public function test_stats_reads_active_hls_listeners(): void
    {
        $stats = Stats::fromArray(['active_listeners' => 15, 'active_hls_listeners' => 3]);

        $this->assertSame(15, $stats->activeListeners);
        $this->assertSame(3, $stats->activeHlsListeners);
    }
}
