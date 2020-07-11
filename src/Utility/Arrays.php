<?php

declare(strict_types=1);

namespace le0daniel\Laravel\ImageEngine\Utility;

final class Arrays
{
    public static function mapKeys(array $array, array $keyMap): array
    {
        $mappedArray = [];

        foreach ($array as $key => $value) {
            $mappedKey = array_key_exists($key, $keyMap)
                ? $keyMap[$key]
                : $key;
            $mappedArray[$mappedKey] = $value;
        }

        return $mappedArray;
    }

    public static function removeNullValues(array $array): array
    {
        return array_filter($array, fn($value) => !is_null($value));
    }

    public static function applyDefaultValues(array $array, array $defaultValues): array
    {
        foreach ($defaultValues as $key => $defaultValue) {
            if (!array_key_exists($key, $array)) {
                $array[$key] = $defaultValue;
            }
        }
        return $array;
    }

}
