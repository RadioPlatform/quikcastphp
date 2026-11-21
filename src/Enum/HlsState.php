<?php

declare(strict_types=1);

namespace Quikcast\Enum;

enum HlsState: string
{
    case Active = 'active';
    case Empty = 'empty';
    case Ended = 'ended';
    case Deleted = 'deleted';
}
