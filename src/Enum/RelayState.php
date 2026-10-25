<?php

declare(strict_types=1);

namespace Quikcast\Enum;

enum RelayState: string
{
    case Connecting = 'connecting';
    case Connected = 'connected';
    case Backoff = 'backoff';
    case Stopped = 'stopped';
}
