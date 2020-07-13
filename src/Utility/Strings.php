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
        if ($index < 0) {
            throw new \Exception('Can not spilt a string at a negative index.');
        }

        return [
            substr($string, 0, $index),
            $index < strlen($string)
                ? substr($string, $index)
                : ''
            ,
        ];
    }

}
