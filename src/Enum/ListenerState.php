<?php

declare(strict_types=1);

namespace Quikcast\Enum;

enum ListenerState: string
{
    case Connected = 'connected';
    case Disconnecting = 'disconnecting';
}
