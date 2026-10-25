<?php

declare(strict_types=1);

namespace Quikcast\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Quikcast\Config;

final class ConfigTest extends TestCase
{
    public function test_trims_trailing_slash(): void
    {
        $config = new Config('http://127.0.0.1:9090/', 'token');

        $this->assertSame('http://127.0.0.1:9090', $config->baseUrl());
    }

    public function test_rejects_empty_token(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config('http://127.0.0.1:9090', '');
    }

    public function test_redacted_hides_token(): void
    {
        $config = new Config('http://127.0.0.1:9090', 'secret');

        $this->assertSame(['base_url' => 'http://127.0.0.1:9090'], $config->redacted());
    }
}
