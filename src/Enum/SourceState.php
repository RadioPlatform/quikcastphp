<?php

declare(strict_types=1);

namespace Quikcast\Enum;

enum SourceState: string
{
    case Connecting = 'connecting';
    case Streaming = 'streaming';
    case Disconnecting = 'disconnecting';
}
