<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:55
 */

namespace le0daniel\Laravel\ImageEngine\Image;


class Signer
{
    /** @var string */
    protected $secret;

    /**
     * SignPayload constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $secret = config('image-engine.key');
        if (empty($secret)) {
            throw new \Exception('No secret defined');
        }

        $this->secret = $secret;
    }

    /**
     * @param string $input
     * @return string
     */
    protected function base64_url_encode(string $input): string
    {
        return strtr(base64_encode($input), '+/=', '._-');
    }

    /**
     * @param string $input
     * @return string
     */
    protected function base64_url_decode(string $input): string
    {
        return base64_decode(strtr($input, '._-', '+/='));
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function signPayload(array $data): array
    {
        asort($data);
        $encoded = $this->jsonEncode($data);
        $signature = hash_hmac('sha256', $encoded, $this->secret);
        return [
            $this->base64_url_encode($encoded),
            $signature
        ];
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function signPayloadToString(array $data): string
    {
        list($payload, $signature) = $this->signPayload($data);
        return $payload . '::' . $signature;
    }

    /**
     * @param string $signedString
     * @return array
     * @throws \Exception
     */
    public function verifyStringAndUnpack(string $signedString)
    {
        list($payload, $hash) = explode('::', $signedString, 2);
        return $this->verifyAndUnpack($payload, $hash);
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    protected function jsonEncode(array $data): string
    {
        if (!$encode = json_encode($data)) {
            throw new \Exception('Could not encode');
        }
        return $encode;
    }

    /**
     * @param string $input
     * @return array
     * @throws \Exception
     */
    protected function jsonDecode(string $input): array
    {
        if (!$decoded = json_decode($input, true)) {
            throw new \Exception('Could not decode');
        }

        return $decoded;
    }

    /**
     * @param string $data
     * @param string $hash
     * @return array
     * @throws \Exception
     */
    public function verifyAndUnpack(string $data, string $hash): array
    {
        $decodedData = $this->base64_url_decode($data);

        if (empty($decodedData) || empty($hash)) {
            throw new \Exception('Could not decode hash');
        }

        $calculatedHash = hash_hmac('sha256', $decodedData, $this->secret);

        if (!hash_equals($calculatedHash, $hash)) {
            throw new \Exception('Signature missmatch');
        }

        return $this->jsonDecode($decodedData);
    }
}