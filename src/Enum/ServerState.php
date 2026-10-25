<?php

declare(strict_types=1);

namespace Quikcast\Enum;

enum ServerState: string
{
    case Accepting = 'accepting';
    case Draining = 'draining';
    case ShuttingDown = 'shutting_down';
}
