<?php

declare(strict_types=1);

namespace Quikcast\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Quikcast\Exception\TransportException;
use Quikcast\Exception\UnauthorizedException;
use Quikcast\Tests\Support\FakeHttp;
use Quikcast\Transport\SourceTransport;

final class SourceTransportTest extends TestCase
{
    private FakeHttp $http;

    private function transport(FakeHttp $http): SourceTransport
    {
        $this->http = $http;

        return new SourceTransport($http->client);
    }

    public function test_update_song_uses_basic_auth_and_query(): void
    {
        $transport = $this->transport(new FakeHttp([
            FakeHttp::response(200, ['Content-Type' => 'text/xml'], '<iceresponse><message>OK</message></iceresponse>'),
        ]));

        $transport->updateSong('http://127.0.0.1:8000', '/radio.mp3', 'source', 'secret', 'Artist - Title');

        $request = $this->http->last();
        $uri = $request->url;
        $this->assertSame('/api/metadata', $request->path());
        $this->assertStringContainsString('mount=%2Fradio.mp3', $uri);
        $this->assertStringContainsString('mode=updinfo', $uri);
        $this->assertStringContainsString('song=Artist%20-%20Title', $uri);
        $this->assertStringStartsWith('Basic ', $request->header('Authorization'));
        $this->assertStringNotContainsString('Bearer', $request->header('Authorization'));
    }

    public function test_update_track_sends_artist_and_title(): void
    {
        $transport = $this->transport(new FakeHttp([FakeHttp::response(200)]));

        $transport->updateTrack('http://127.0.0.1:8000', 'radio.mp3', 'source', 'secret', 'Title', 'Artist');

        $query = $this->http->last()->query();
        $this->assertStringContainsString('artist=Artist', $query);
        $this->assertStringContainsString('title=Title', $query);
        $this->assertStringNotContainsString('song=', $query);
    }

    public function test_update_song_401_maps_to_unauthorized(): void
    {
        $transport = $this->transport(new FakeHttp([FakeHttp::response(401)]));

        $this->expectException(UnauthorizedException::class);
        $transport->updateSong('http://127.0.0.1:8000', '/radio.mp3', 'source', 'wrong', 'Artist - Title');
    }

    public function test_update_song_500_maps_to_transport(): void
    {
        $transport = $this->transport(new FakeHttp([FakeHttp::response(500)]));

        $this->expectException(TransportException::class);
        $transport->updateSong('http://127.0.0.1:8000', '/radio.mp3', 'source', 'secret', 'Artist - Title');
    }
}
