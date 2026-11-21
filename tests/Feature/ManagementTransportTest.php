<?php

declare(strict_types=1);

namespace Quikcast\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Quikcast\Config;
use Quikcast\Exception\ConflictException;
use Quikcast\Exception\EndpointNotFoundException;
use Quikcast\Exception\InvalidPaginationException;
use Quikcast\Exception\MethodNotAllowedException;
use Quikcast\Exception\MountNotFoundException;
use Quikcast\Exception\ServerDrainingException;
use Quikcast\Exception\SourceNotConnectedException;
use Quikcast\Exception\TransportException;
use Quikcast\Exception\UnauthorizedException;
use Quikcast\Tests\Support\FakeHttp;
use Quikcast\Transport\ManagementTransport;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ManagementTransportTest extends TestCase
{
    private FakeHttp $http;

    private function transport(FakeHttp $http): ManagementTransport
    {
        $this->http = $http;

        return new ManagementTransport(
            new Config('http://127.0.0.1:9090', 'test-token'),
            $http->client
        );
    }

    public function test_sends_bearer_token_and_accept_headers(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], '{"name":"Quikcast"}'),
        ]));

        $transport->request('GET', '/api/server');

        $request = $this->http->last();
        $this->assertSame('Bearer test-token', $request->header('Authorization'));
        $this->assertSame('application/json', $request->header('Accept'));
    }

    public function test_post_sends_empty_body_with_zero_content_length(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], '{"state":"draining"}'),
        ]));

        $transport->request('POST', '/api/server/drain');

        $request = $this->http->last();
        $this->assertSame('0', $request->header('Content-Length'));
        $this->assertSame('', $request->body);
    }

    public function test_delete_with_query_string(): void
    {
        $transport = $this->transport(new FakeHttp([FakeHttp::response(204)]));

        $this->assertNull($transport->request('DELETE', '/api/sources/radio.mp3', ['generation' => 7]));

        $uri = $this->http->last()->url;
        $this->assertStringContainsString('generation=7', $uri);
    }

    public function test_204_returns_null(): void
    {
        $transport = $this->transport(new FakeHttp([FakeHttp::response(204)]));

        $this->assertNull($transport->request('DELETE', '/api/listeners/abc'));
    }

    public function test_unauthorized_maps(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(401, ['WWW-Authenticate' => 'Bearer realm="Quikcast management"'], '{"error":{"code":"unauthorized","message":"Authentication required"}}'),
        ]));

        $this->expectException(UnauthorizedException::class);
        $transport->request('GET', '/api/server');
    }

    public function test_mount_not_found_maps(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(404, [], '{"error":{"code":"mount_not_found","message":"Mount not found"}}'),
        ]));

        $this->expectException(MountNotFoundException::class);
        $transport->request('GET', '/api/mounts/missing.mp3');
    }

    public function test_invalid_pagination_maps(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(400, [], '{"error":{"code":"invalid_pagination","message":"Invalid listener pagination"}}'),
        ]));

        $this->expectException(InvalidPaginationException::class);
        $transport->request('GET', '/api/listeners', ['mount' => '/radio.mp3', 'limit' => 201]);
    }

    public function test_stale_generation_conflict_maps(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(409, [], '{"error":{"code":"conflict","message":"Source generation changed"}}'),
        ]));

        $this->expectException(ConflictException::class);
        $transport->request('DELETE', '/api/sources/radio.mp3', ['generation' => 1]);
    }

    public function test_source_not_connected_maps(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(409, [], '{"error":{"code":"source_not_connected","message":"Source not connected"}}'),
        ]));

        $this->expectException(SourceNotConnectedException::class);
        $transport->request('DELETE', '/api/sources/radio.mp3');
    }

    public function test_drain_rejection_maps_to_503(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(503, [], '{"error":{"code":"server_draining","message":"Server draining"}}'),
        ]));

        $this->expectException(ServerDrainingException::class);
        $transport->request('POST', '/api/relays/remote.mp3');
    }

    public function test_method_not_allowed_captures_allow_header(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(405, ['Allow' => 'GET'], '{"error":{"code":"method_not_allowed","message":"Method not allowed"}}'),
        ]));

        try {
            $transport->request('POST', '/api/server');
            $this->fail('Expected MethodNotAllowedException');
        } catch (MethodNotAllowedException $e) {
            $this->assertSame('GET', $e->allow);
        }
    }

    public function test_unknown_404_maps_to_endpoint_not_found(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(404, [], '{"error":{"code":"endpoint_not_found","message":"Not found"}}'),
        ]));

        $this->expectException(EndpointNotFoundException::class);
        $transport->request('GET', '/api/nope');
    }

    public function test_malformed_json_throws_transport_exception(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(200, ['Content-Type' => 'application/json'], 'not-json{{{'),
        ]));

        $this->expectException(TransportException::class);
        $transport->request('GET', '/api/server');
    }

    public function test_network_failure_maps_to_transport_exception(): void
    {
        $transport = $this->transport(new FakeHttp([
            new MockResponse('', ['error' => 'connection refused']),
        ]));

        $this->expectException(TransportException::class);
        $transport->request('GET', '/api/server');
    }
}
