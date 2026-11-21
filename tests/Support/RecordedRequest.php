<?php

declare(strict_types=1);

namespace Quikcast\Tests\Support;

/**
 * One request as the HTTP client was asked to send it.
 */
final readonly class RecordedRequest
{
    /**
     * @param  array<string, list<string>>  $headers  lowercase name => "Name: value" lines
     */
    public function __construct(
        public string $method,
        public string $url,
        private array $headers,
        public string $body,
    ) {}

    public function path(): string
    {
        return (string) parse_url($this->url, PHP_URL_PATH);
    }

    public function query(): string
    {
        return (string) parse_url($this->url, PHP_URL_QUERY);
    }

    public function header(string $name): string
    {
        $lines = $this->headers[strtolower($name)] ?? [];

        return $lines === [] ? '' : trim(substr($lines[0], strlen($name) + 1));
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }
}
