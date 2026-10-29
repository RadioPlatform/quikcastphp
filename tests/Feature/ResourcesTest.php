<?php

declare(strict_types=1);

namespace Quikcast\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Quikcast\Enum\ServerState;
use Quikcast\Exception\ListenerNotFoundException;
use Quikcast\Exception\MountNotFoundException;
use Quikcast\QuikcastClient;
use Quikcast\Resources\MetricsResource;

final class ResourcesTest extends TestCase
{
    /** @var list<RequestInterface> */
    private array $recorded = [];

    /** @param list<Response> $responses */
    private function client(array $responses): QuikcastClient
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push($this->recorder());

        return QuikcastClient::create(
            'http://127.0.0.1:9090',
            'test-token',
            new Client(['handler' => $stack, 'http_errors' => false])
        );
    }

    private function recorder(): callable
    {
        return function (callable $handler): callable {
            return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
                $this->recorded[] = $request;

                $promise = $handler($request, $options);

                if (! $promise instanceof PromiseInterface) {
                    self::fail('Handler did not return a promise');
                }

                return $promise;
            };
        };
    }

    private function lastRequest(int $index = 0): RequestInterface
    {
        $request = $this->recorded[$index] ?? null;

        if (! $request instanceof RequestInterface) {
            self::fail('No recorded request at index '.$index);
        }

        return $request;
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
            new Response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('server.json')),
        ]);

        $server = $client->server()->get();

        $this->assertSame(ServerState::Accepting, $server->state);
        $this->assertSame('/api/server', $this->lastRequest()->getUri()->getPath());
    }

    public function test_drain_server_posts(): void
    {
        $client = $this->client([
            new Response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('server-draining.json')),
        ]);

        $server = $client->server()->drain();

        $this->assertTrue($server->isDraining());
        $this->assertSame('POST', $this->lastRequest()->getMethod());
    }

    public function test_list_mounts(): void
    {
        $client = $this->client([
            new Response(200, ['Content-Type' => 'application/json'], '['.$this->fixtureBody('mount.json').']'),
        ]);

        $mounts = $client->mounts()->list();

        $this->assertCount(1, $mounts);
        $this->assertSame('/radio.mp3', $mounts[0]->path);
    }

    public function test_get_mount_encodes_nested_path(): void
    {
        $client = $this->client([
            new Response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('mount.json')),
        ]);

        $client->mounts()->get('nested/radio.mp3');

        $this->assertSame('/api/mounts/nested/radio.mp3', $this->lastRequest()->getUri()->getPath());
    }

    public function test_get_source_returns_null_for_disconnected(): void
    {
        $client = $this->client([new Response(200, ['Content-Type' => 'application/json'], 'null')]);

        $this->assertNull($client->sources()->get('/radio.mp3'));
    }

    public function test_disconnect_source_without_generation(): void
    {
        $client = $this->client([new Response(204)]);

        $client->sources()->disconnect('/radio.mp3');

        $request = $this->lastRequest();
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('', $request->getUri()->getQuery());
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
            new Response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('listeners-page.json')),
            new Response(200, ['Content-Type' => 'application/json'], '{"items":[],"next_after":null}'),
        ]);

        $all = $client->listeners()->all('/radio.mp3', 1);

        $this->assertCount(1, $all);
        $this->assertCount(2, $this->recorded);
        $secondQuery = [];
        parse_str($this->lastRequest(1)->getUri()->getQuery(), $secondQuery);
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
            new Response(404, [], '{"error":{"code":"listener_not_found","message":"Listener not found"}}'),
        ]);

        $this->expectException(ListenerNotFoundException::class);
        $client->listeners()->get('0123456789abcdef0123456789abcdef');
    }

    public function test_hls_lists_and_gets(): void
    {
        $client = $this->client([
            new Response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('hls-streams.json')),
            new Response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('rendition.json')),
        ]);

        $streams = $client->hls()->listStreams();
        $rendition = $client->hls()->getRendition('radio', 'audio');

        $this->assertCount(1, $streams);
        $this->assertSame('audio', $rendition->name);
        $this->assertSame('/api/hls/radio/renditions/audio', $this->lastRequest(1)->getUri()->getPath());
    }

    public function test_relay_restart_posts_with_revision(): void
    {
        $client = $this->client([
            new Response(202, ['Content-Type' => 'application/json'], $this->fixtureBody('relay.json')),
        ]);

        $snapshot = $client->relays()->restart('/remote.mp3', 4);

        $this->assertSame(4, $snapshot->revision);
        $request = $this->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('revision=4', $request->getUri()->getQuery());
    }

    public function test_unknown_mount_throws(): void
    {
        $client = $this->client([
            new Response(404, [], '{"error":{"code":"mount_not_found","message":"Mount not found"}}'),
        ]);

        $this->expectException(MountNotFoundException::class);
        $client->mounts()->get('/missing.mp3');
    }

    public function test_history_sends_canonical_mount_and_minutes(): void
    {
        $client = $this->client([
            new Response(200, ['Content-Type' => 'application/json'], $this->fixtureBody('history.json')),
        ]);

        $history = $client->history()->get('nested/radio.mp3', 5);

        $this->assertSame(41, $history->peakListeners24h);
        $this->assertSame('/api/history', $this->lastRequest()->getUri()->getPath());
        $this->assertSame('mount=%2Fnested%2Fradio.mp3&minutes=5', $this->lastRequest()->getUri()->getQuery());
    }

    public function test_history_validates_minutes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client([])->history()->get(null, 1441);
    }

    public function test_metrics_returns_prometheus_text(): void
    {
        $mock = new MockHandler([new Response(200, [], "# TYPE ready gauge\nready 1\n")]);
        $http = new Client(['handler' => HandlerStack::create($mock), 'http_errors' => false]);

        $text = (new MetricsResource('http://127.0.0.1:9090/', $http))->text();

        $this->assertStringContainsString('ready 1', $text);
    }
}
