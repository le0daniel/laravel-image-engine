<?php

namespace le0daniel\Laravel\ImageEngine\Image;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use le0daniel\Laravel\ImageEngine\Utility\Arrays;
use le0daniel\Laravel\ImageEngine\Utility\Base64;
use le0daniel\Laravel\ImageEngine\Utility\Json;

/**
 * Class ImageRepresentation
 * @package le0daniel\Laravel\ImageEngine\Image
 *
 * @property-read string $filePath
 * @property-read string $size
 * @property-read null|int $expires
 * @property-read null|Carbon $expiresCarbon
 * @property-read string $diskName
 * @property-read int|null $timestamp
 * @property-read string $extension
 * @property-read bool $isConfidential
 * @property-read bool $isExpired
 */
class ImageRepresentation
{
    public const DEFAULT_DISK_NAME = 'local';
    private const SERIALIZE_KEY_MAP = [
        'filePath' => 'p',
        'size' => 's',
        'expires' => 'e',
        'diskName' => 'd',
    ];
    private const IMAGE_CACHE_MAX_AGE = 31557600;
    private array $attributes = [];

    private function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string $filePath
     * @param string $size
     * @param null|Carbon|int $expires
     * @param string $diskName
     * @return static
     */
    public static function from(
        string $filePath,
        string $size,
        $expires = null,
        string $diskName = self::DEFAULT_DISK_NAME
    ): self {
        return new self(
            [
                'filePath' => $filePath,
                'size' => $size,
                'expires' => $expires instanceof Carbon
                    ? $expires->getTimestamp()
                    : $expires,
                'diskName' => $diskName === self::DEFAULT_DISK_NAME
                    ? null
                    : $diskName
                ,
            ]
        );
    }

    public static function fromSerialized(string $serialized): self
    {
        $mappedAttributes = Json::decode(Base64::urlDecode($serialized), ['e' => null, 'd' => null]);
        $attributes = Arrays::mapKeys(
            $mappedAttributes,
            array_flip(self::SERIALIZE_KEY_MAP)
        );
        return new self($attributes);
    }

    public function __set($name, $value)
    {
        throw new \RuntimeException("Can not modify immutable ImageRepresentation. Tried to modify `{$name}`");
    }

    public function __isset(string $name)
    {
        return array_key_exists($name, $this->attributes);
    }

    private function getValue(string $name, $value)
    {
        switch ($name) {
            case 'timestamp':
                return $this->expires;
            case 'disk':
                return $value ?? self::DEFAULT_DISK_NAME;
            case 'extension':
                return pathinfo($this->filePath, PATHINFO_EXTENSION);
            case 'isConfidential':
                return (bool)$this->expires;
            case 'expiresCarbon':
                return $this->isConfidential ? Carbon::createFromTimestamp($this->expires) : null;
            case 'isExpired':
                return $this->isConfidential ? $this->expiresCarbon->isPast() : false;
        }

        return $value;
    }

    private function timeToExpireInSeconds(): int
    {
        return $this->isConfidential
            ? $this->expires - time()
            : self::IMAGE_CACHE_MAX_AGE;
    }

    public function cacheControlHeaders(): array
    {
        $maxAgeInSeconds = min($this->timeToExpireInSeconds(), self::IMAGE_CACHE_MAX_AGE);

        return [
            'Cache-Control' => "max-age={$maxAgeInSeconds}, public",
        ];
    }

    public function __get(string $name)
    {
        return $this->__isset($name)
            ? $this->getValue($name, $this->attributes[$name])
            : $this->getValue($name, null);
    }

    public function toArray()
    {
        $array = Arrays::removeNullValues(
            Arrays::mapKeys($this->attributes, self::SERIALIZE_KEY_MAP)
        );
        asort($array);
        return $array;
    }

    public function serialize(): string
    {
        return Base64::urlEncode(
            Json::encode($this->toArray())
        );
    }
}
