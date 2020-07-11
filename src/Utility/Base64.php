<?php

declare(strict_types=1);

namespace le0daniel\Laravel\ImageEngine\Utility;

final class Base64
{

    public static function encode(string $string): string
    {
        return base64_encode($string);
    }

    public static function decode(string $encodedString): string
    {
        return base64_decode($encodedString);
    }

    public static function urlEncode(string $string): string
    {
        $urlSafe = strtr(self::encode($string), '+/', '-_');
        return rtrim($urlSafe, '=');
    }

    public static function urlDecode(string $urlEncodedString): string
    {
        return self::decode(
            strtr($urlEncodedString, '-_', '+/')
        );
    }

}
