<?php

declare(strict_types=1);

namespace Quikcast\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Quikcast\Exception\TransportException;
use Quikcast\Exception\UnauthorizedException;
use Quikcast\Transport\SourceTransport;

final class SourceTransportTest extends TestCase
{
    /** @var list<RequestInterface> */
    private array $recorded = [];

    private function transport(MockHandler $mock): SourceTransport
    {
        $stack = HandlerStack::create($mock);
        $stack->push($this->recorder());

        return new SourceTransport(new Client(['handler' => $stack, 'http_errors' => false]));
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

    public function test_update_song_uses_basic_auth_and_query(): void
    {
        $transport = $this->transport(new MockHandler([
            new Response(200, ['Content-Type' => 'text/xml'], '<iceresponse><message>OK</message></iceresponse>'),
        ]));

        $transport->updateSong('http://127.0.0.1:8000', '/radio.mp3', 'source', 'secret', 'Artist - Title');

        $request = $this->lastRequest();
        $uri = (string) $request->getUri();
        $this->assertStringContainsString('mode=updinfo', $uri);
        $this->assertStringContainsString('song=', $uri);
        $this->assertStringStartsWith('Basic ', $request->getHeaderLine('Authorization'));
        $this->assertStringNotContainsString('Bearer', $request->getHeaderLine('Authorization'));
    }

    public function test_update_song_401_maps_to_unauthorized(): void
    {
        $transport = $this->transport(new MockHandler([new Response(401)]));

        $this->expectException(UnauthorizedException::class);
        $transport->updateSong('http://127.0.0.1:8000', '/radio.mp3', 'source', 'wrong', 'Artist - Title');
    }

    public function test_update_song_500_maps_to_transport(): void
    {
        $transport = $this->transport(new MockHandler([new Response(500)]));

        $this->expectException(TransportException::class);
        $transport->updateSong('http://127.0.0.1:8000', '/radio.mp3', 'source', 'secret', 'Artist - Title');
    }
}
