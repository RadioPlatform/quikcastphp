<?php

declare(strict_types=1);

namespace Quikcast\Enum;

enum HlsState: string
{
    case Active = 'active';
    case Ended = 'ended';
    case Deleted = 'deleted';
}
