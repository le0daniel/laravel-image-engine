<?php

declare(strict_types=1);

namespace le0daniel\Laravel\ImageEngine\Utility;

final class Strings
{

    /**
     * @param string $string
     * @param int $index
     * @return string[]
     */
    public static function splitAtIndex(string $string, int $index): array
    {
        return [
            substr($string, 0, $index),
            $part2 = substr($string, $index),
        ];
    }

}
