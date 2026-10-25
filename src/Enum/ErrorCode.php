<?php

declare(strict_types=1);

namespace Quikcast\Enum;

enum ErrorCode: string
{
    case Unauthorized = 'unauthorized';
    case InvalidRequest = 'invalid_request';
    case InvalidPagination = 'invalid_pagination';
    case MethodNotAllowed = 'method_not_allowed';
    case EndpointNotFound = 'endpoint_not_found';
    case MountNotFound = 'mount_not_found';
    case ListenerNotFound = 'listener_not_found';
    case HlsNotFound = 'hls_not_found';
    case RelayNotFound = 'relay_not_found';
    case SourceNotConnected = 'source_not_connected';
    case Conflict = 'conflict';
    case ServerDraining = 'server_draining';
    case InternalError = 'internal_error';
    case Unknown = 'unknown';
}
