<?php

declare(strict_types=1);

namespace Quikcast\Support;

use InvalidArgumentException;

final class MountPath
{
    /**
     * Normalize caller input to a canonical mount path with one leading slash.
     */
    public static function canonicalize(string $mount): string
    {
        $trimmed = trim($mount);

        if ($trimmed === '' || $trimmed === '/') {
            throw new InvalidArgumentException('Mount path must not be empty.');
        }

        $collapsed = (string) preg_replace('#/+#', '/', $trimmed);

        if (! str_starts_with($collapsed, '/')) {
            $collapsed = '/'.$collapsed;
        }

        if (strlen($collapsed) < 2) {
            throw new InvalidArgumentException('Mount path must not be empty.');
        }

        return $collapsed;
    }

    /**
     * Encode a canonical mount path segment-by-segment for use in /api URLs.
     */
    public static function encodeForApi(string $mount): string
    {
        $canonical = self::canonicalize($mount);
        $segments = explode('/', ltrim($canonical, '/'));

        $encoded = array_map(rawurlencode(...), $segments);

        return implode('/', $encoded);
    }
}
