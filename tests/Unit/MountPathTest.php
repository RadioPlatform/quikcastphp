<?php

declare(strict_types=1);

namespace Quikcast\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Quikcast\Support\MountPath;

final class MountPathTest extends TestCase
{
    public function test_canonicalize_adds_leading_slash(): void
    {
        $this->assertSame('/radio.mp3', MountPath::canonicalize('radio.mp3'));
        $this->assertSame('/radio.mp3', MountPath::canonicalize('/radio.mp3'));
        $this->assertSame('/nested/radio.mp3', MountPath::canonicalize('nested/radio.mp3'));
        $this->assertSame('/nested/radio.mp3', MountPath::canonicalize('//nested//radio.mp3'));
    }

    public function test_canonicalize_rejects_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MountPath::canonicalize('');
    }

    public function test_encode_for_api_encodes_segments(): void
    {
        $this->assertSame('nested/radio.mp3', MountPath::encodeForApi('/nested/radio.mp3'));
        $this->assertSame('my%20radio.mp3', MountPath::encodeForApi('/my radio.mp3'));
    }
}
