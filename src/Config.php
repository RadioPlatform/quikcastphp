<?php

declare(strict_types=1);

namespace Quikcast;

use InvalidArgumentException;

final readonly class Config
{
    public function __construct(
        public string $baseUrl,
        public string $token,
    ) {
        if (trim($this->baseUrl) === '') {
            throw new InvalidArgumentException('Base URL must not be empty.');
        }

        if ($this->token === '') {
            throw new InvalidArgumentException('Management token must not be empty.');
        }
    }

    public function baseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    /**
     * Safe representation without secret material.
     *
     * @return array{base_url: string}
     */
    public function redacted(): array
    {
        return ['base_url' => $this->baseUrl()];
    }
}
