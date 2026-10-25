<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class ListenerPage extends Dto
{
    /**
     * @param  list<ListenerInfo>  $items
     */
    public function __construct(
        public array $items,
        public ?string $nextAfter,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $items = [];

        foreach (self::mapList($data['items'] ?? []) as $item) {
            $items[] = ListenerInfo::fromArray($item);
        }

        $nextAfter = isset($data['next_after']) && is_string($data['next_after']) ? $data['next_after'] : null;

        return new self($items, $nextAfter);
    }

    public function hasMore(): bool
    {
        return $this->nextAfter !== null;
    }
}
