<?php

declare(strict_types=1);

namespace Quikcast\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Quikcast\Config;
use Quikcast\Dto\ErrorResponse;
use Quikcast\Enum\ErrorCode;
use Quikcast\Exception\ConflictException;
use Quikcast\Exception\EndpointNotFoundException;
use Quikcast\Exception\HlsNotFoundException;
use Quikcast\Exception\InternalErrorException;
use Quikcast\Exception\InvalidPaginationException;
use Quikcast\Exception\InvalidRequestException;
use Quikcast\Exception\ListenerNotFoundException;
use Quikcast\Exception\MethodNotAllowedException;
use Quikcast\Exception\MountNotFoundException;
use Quikcast\Exception\QuikcastException;
use Quikcast\Exception\RelayNotFoundException;
use Quikcast\Exception\ServerDrainingException;
use Quikcast\Exception\SourceNotConnectedException;
use Quikcast\Exception\TransportException;
use Quikcast\Exception\UnauthorizedException;

/**
 * Guzzle wrapper for the Bearer-authenticated management API.
 *
 * All management requests carry no body; POST/DELETE explicitly send
 * Content-Length: 0 and never use chunked encoding or Expect.
 */
final class ManagementTransport
{
    public const USER_AGENT = 'quikcast-php/0.1.0';

    private ClientInterface $http;

    public function __construct(
        private readonly Config $config,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new Client(['http_errors' => false]);
    }

    /**
     * @param  array<string, string|int>  $query
     * @return mixed decoded JSON (array, null for empty/204)
     */
    public function request(string $method, string $path, array $query = []): mixed
    {
        $url = $this->config->baseUrl().$path;

        if ($query !== []) {
            $url .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $options = [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer '.$this->config->token,
                'Accept' => 'application/json',
                'User-Agent' => self::USER_AGENT,
            ],
        ];

        if ($method === 'POST' || $method === 'DELETE') {
            $options['headers']['Content-Length'] = '0';
            $options['body'] = '';
        }

        try {
            $response = $this->http->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw new TransportException('HTTP transport failure: '.$e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();

        if ($status === 204) {
            return null;
        }

        $body = (string) $response->getBody();

        if ($status >= 200 && $status < 300) {
            if ($body === '') {
                return null;
            }

            try {
                return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new TransportException('Invalid JSON response from Quikcast: '.$e->getMessage(), 0, $e);
            }
        }

        $allow = $response->hasHeader('Allow') ? implode(', ', $response->getHeader('Allow')) : null;

        $decoded = null;
        if ($body !== '') {
            try {
                $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $decoded = null;
            }
        }

        $error = ErrorResponse::fromArray($decoded);

        throw self::toException($status, $error, $allow);
    }

    private static function toException(int $status, ErrorResponse $error, ?string $allow): QuikcastException
    {
        $message = $error->message !== '' ? $error->message : 'Quikcast request failed';

        return match ($error->code) {
            ErrorCode::Unauthorized => new UnauthorizedException($message, $status, $error->code, $allow),
            ErrorCode::InvalidPagination => new InvalidPaginationException($message, $status, $error->code, $allow),
            ErrorCode::InvalidRequest => new InvalidRequestException($message, $status, $error->code, $allow),
            ErrorCode::MountNotFound => new MountNotFoundException($message, $status, $error->code, $allow),
            ErrorCode::ListenerNotFound => new ListenerNotFoundException($message, $status, $error->code, $allow),
            ErrorCode::HlsNotFound => new HlsNotFoundException($message, $status, $error->code, $allow),
            ErrorCode::RelayNotFound => new RelayNotFoundException($message, $status, $error->code, $allow),
            ErrorCode::SourceNotConnected => new SourceNotConnectedException($message, $status, $error->code, $allow),
            ErrorCode::Conflict => new ConflictException($message, $status, $error->code, $allow),
            ErrorCode::ServerDraining => new ServerDrainingException($message, $status, $error->code, $allow),
            ErrorCode::MethodNotAllowed => new MethodNotAllowedException($message, $status, $error->code, $allow),
            ErrorCode::EndpointNotFound => new EndpointNotFoundException($message, $status, $error->code, $allow),
            ErrorCode::InternalError => new InternalErrorException($message, $status, $error->code, $allow),
            default => match (true) {
                $status === 401 => new UnauthorizedException($message, $status, $error->code, $allow),
                $status === 404 => new EndpointNotFoundException($message, $status, $error->code, $allow),
                $status === 405 => new MethodNotAllowedException($message, $status, $error->code, $allow),
                $status === 409 => new ConflictException($message, $status, $error->code, $allow),
                $status === 503 => new ServerDrainingException($message, $status, $error->code, $allow),
                $status >= 500 => new InternalErrorException($message, $status, $error->code, $allow),
                default => new InvalidRequestException($message, $status, $error->code, $allow),
            },
        };
    }
}
