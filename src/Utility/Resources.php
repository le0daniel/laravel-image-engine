<?php

declare(strict_types=1);

namespace le0daniel\Laravel\ImageEngine\Utility;

final class Resources
{

    /**
     * @param string $path
     * @param string $mode
     * @return resource
     */
    public static function open(string $path, string $mode)
    {
        $resource = fopen($path, $mode);
        if (!is_resource($resource)) {
            throw new \RuntimeException("Could not open resource {$path} in mode: {$mode}");
        }

        return $resource;
    }

    public static function close(...$resources): void
    {
        foreach ($resources as $resource) {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    public static function auto(\Closure $closure, string $mode, string ...$paths)
    {
        $resources = array_map(fn(string $path) => self::open($path, $mode), $paths);
        $closure(...$resources);
        self::close(...$resources);
    }

}
