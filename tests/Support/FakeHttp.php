<?php

declare(strict_types=1);

namespace Quikcast\Tests\Support;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Queues canned responses on a Symfony MockHttpClient and records what was sent.
 */
final class FakeHttp
{
    /** @var list<RecordedRequest> */
    public array $requests = [];

    public readonly MockHttpClient $client;

    /**
     * @param  list<MockResponse>  $responses
     */
    public function __construct(array $responses)
    {
        $this->client = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses): MockResponse {
            $body = $options['body'] ?? '';
            /** @var array<string, list<string>> $headers */
            $headers = $options['normalized_headers'] ?? [];

            $this->requests[] = new RecordedRequest(
                $method,
                $url,
                $headers,
                is_string($body) ? $body : '',
            );

            $next = array_shift($responses);

            if ($next === null) {
                throw new \LogicException('No queued response for '.$method.' '.$url);
            }

            return $next;
        });
    }

    /**
     * @param  array<string, string>  $headers
     */
    public static function response(int $status = 200, array $headers = [], string $body = ''): MockResponse
    {
        $lines = [];
        foreach ($headers as $name => $value) {
            $lines[] = $name.': '.$value;
        }

        return new MockResponse($body, ['http_code' => $status, 'response_headers' => $lines]);
    }

    /**
     * The request at $index, or the most recent one when omitted.
     */
    public function last(?int $index = null): RecordedRequest
    {
        $index ??= count($this->requests) - 1;

        return $this->requests[$index] ?? throw new \LogicException('No recorded request at index '.$index);
    }
}
