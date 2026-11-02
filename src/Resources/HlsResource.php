<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use Quikcast\Dto\RenditionInfo;
use Quikcast\Dto\StreamInfo;
use Quikcast\Transport\ManagementTransport;

final readonly class HlsResource
{
    public function __construct(private ManagementTransport $transport) {}

    /**
     * @return list<StreamInfo>
     */
    public function listStreams(): array
    {
        /** @var list<array<string, mixed>> $data */
        $data = $this->transport->request('GET', '/api/hls');

        return array_map(StreamInfo::fromArray(...), $data);
    }

    public function getStream(string $stream): StreamInfo
    {
        /** @var array<string, mixed> $data */
        $data = $this->transport->request('GET', '/api/hls/'.rawurlencode($stream));

        return StreamInfo::fromArray($data);
    }

    /**
     * @return list<RenditionInfo>
     */
    public function listRenditions(string $stream): array
    {
        /** @var list<array<string, mixed>> $data */
        $data = $this->transport->request('GET', '/api/hls/'.rawurlencode($stream).'/renditions');

        return array_map(RenditionInfo::fromArray(...), $data);
    }

    public function getRendition(string $stream, string $rendition): RenditionInfo
    {
        /** @var array<string, mixed> $data */
        $data = $this->transport->request(
            'GET',
            '/api/hls/'.rawurlencode($stream).'/renditions/'.rawurlencode($rendition)
        );

        return RenditionInfo::fromArray($data);
    }
}
