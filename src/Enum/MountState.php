<?php

declare(strict_types=1);

namespace Quikcast\Enum;

enum MountState: string
{
    case Accepting = 'accepting';
    case Draining = 'draining';
    case Closed = 'closed';
}
