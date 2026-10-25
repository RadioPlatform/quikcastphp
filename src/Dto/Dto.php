<?php

declare(strict_types=1);

namespace Quikcast\Dto;

/** @internal shared array helpers for DTO factories. */
abstract readonly class Dto
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected static function str(array $data, string $key, string $default = ''): string
    {
        return isset($data[$key]) && is_string($data[$key]) ? $data[$key] : $default;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected static function nullableStr(array $data, string $key): ?string
    {
        return isset($data[$key]) && is_string($data[$key]) ? $data[$key] : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected static function int(array $data, string $key, int $default = 0): int
    {
        if (! isset($data[$key])) {
            return $default;
        }

        $value = $data[$key];

        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : $default);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected static function nullableInt(array $data, string $key): ?int
    {
        if (! array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        $value = $data[$key];

        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected static function bool(array $data, string $key, bool $default = false): bool
    {
        return isset($data[$key]) && is_bool($data[$key]) ? $data[$key] : $default;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected static function nullableBool(array $data, string $key): ?bool
    {
        return array_key_exists($key, $data) && is_bool($data[$key]) ? $data[$key] : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function arr(array $data, string $key): array
    {
        return self::map($data[$key] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, int>
     */
    protected static function intMap(array $data, string $key): array
    {
        $out = [];

        foreach (self::arr($data, $key) as $k => $v) {
            if (is_int($v)) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    /**
     * Coerce decoded JSON-ish data into a string-keyed map.
     *
     * @return array<string, mixed>
     */
    protected static function map(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];

        foreach ($value as $k => $v) {
            if (is_string($k)) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    /**
     * Coerce decoded JSON-ish data into a list of string-keyed maps.
     *
     * @return list<array<string, mixed>>
     */
    protected static function mapList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];

        foreach ($value as $item) {
            if (is_array($item)) {
                $out[] = self::map($item);
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    protected static function strList(array $data, string $key): array
    {
        $value = $data[$key] ?? [];

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_string(...)));
    }
}
