<?php

namespace le0daniel\Laravel\ImageEngine\Utility;

final class Signatures
{
    private const SIGNATURE_REGEX = '/^[^:]*::[a-zA-Z0-9\-\_]+$/';

    public static function sign(string $secret, string $stringToSign): string
    {
        $signature = self::calculateSignature($secret, $stringToSign);
        return $stringToSign . '::' . Base64::urlEncode($signature);
    }

    public static function verifyAndReturnPayloadString(string $secret, string $signedString): string
    {
        self::verifyStructure($signedString);
        [$payload, $userProvidedSignature] = explode('::', $signedString, 2);
        self::verifySignature($secret, $payload, $userProvidedSignature);
        return $payload;
    }

    private static function calculateSignature(string $secret, string $stringToSign): string
    {
        return hash_hmac('sha256', $stringToSign, $secret, true);
    }

    private static function verifyStructure(string $signedString): void
    {
        if (!preg_match(self::SIGNATURE_REGEX, $signedString, $matches, PREG_OFFSET_CAPTURE, 0)) {
            throw new SignatureException('The signed string is of invalid structure');
        }
    }

    private static function verifySignature(string $secret, $payload, $userProvidedSignature): void
    {
        $signature = self::calculateSignature($secret, $payload);

        if (!hash_equals($signature, Base64::urlDecode($userProvidedSignature))) {
            throw new SignatureException('Signature mismatches.');
        }
    }

}
