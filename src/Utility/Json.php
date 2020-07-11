<?php

declare(strict_types=1);

namespace le0daniel\Laravel\ImageEngine\Utility;

final class Json
{

    public static function encode(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public static function decode(string $data, ?array $defaultValues): array
    {
        $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        return $defaultValues
            ? Arrays::applyDefaultValues($data, $defaultValues)
            : $data;
    }

}
