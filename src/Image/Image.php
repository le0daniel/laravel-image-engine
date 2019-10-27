<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:57
 */

namespace le0daniel\Laravel\ImageEngine\Image;


use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Adapter\Local;

class Image
{
    protected $filePath;
    protected $size;
    protected $expires;

    /** @var FilesystemAdapter */
    protected $disk;
    protected $diskName;

    /**
     * Returns the extension
     *
     * @var string
     */
    protected $extension;

    /**
     * Create an image
     *
     * @param array $payload
     * @return Image
     */
    public static function createFromPayload(array $payload)
    {
        return new self(
            $payload['p'],
            $payload['s'],
            ($payload['e'] ?? false) ? Carbon::createFromTimestamp($payload['e']) : null,
            $payload['d'] ?? 'local'
        );
    }

    /**
     * Image constructor.
     * @param string $filePath
     * @param string $size
     * @param Carbon|null $expires
     * @param string $diskName
     */
    public function __construct(string $filePath, string $size, ?Carbon $expires = null, string $diskName = 'local')
    {
        $this->diskName = $diskName;
        $this->disk = \Storage::disk($diskName);
        $this->filePath = $filePath;
        $this->size = $size;
        $this->expires = $expires;

        // Calculate Parts
        $this->extension = pathinfo($filePath, PATHINFO_EXTENSION);
    }

    /**
     * returns the image size
     *
     * @return string
     */
    public function size(): string
    {
        return $this->size;
    }

    /**
     * Returns the timestamp
     *
     * @return int|null
     */
    public function timestamp(): ?int
    {
        return $this->expires ? $this->expires->getTimestamp() : null;
    }

    /**
     * @return string
     */
    public function extension(): string
    {
        return $this->extension;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function realPath(): string
    {
        // Return path from local disk
        if ($this->disk->getDriver()->getAdapter() instanceof Local) {
            return realpath($this->disk->getDriver()->getAdapter()->getPathPrefix() . '/' . $this->filePath);
        }

        $cachePath = config('image-engine.original_cache_dir') . '/' . md5($this->filePath) . '.' .$this->extension();
        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        // Download and cache the file
        if (!file_exists($cachePath)) {
            $resource = fopen($cachePath, 'a');
            $this->disk->writeStream($this->filePath, $resource);
            fclose($resource);
        }

        return realpath($cachePath);
    }

    /**
     * @return bool
     */
    public function isConfidential(): bool
    {
        return !!$this->expires;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->isConfidential()) {
            return false;
        }

        return $this->expires->isPast();
    }

    /**
     * @return int
     */
    protected function timeToExpireInSeconds(): int
    {
        return $this->expires->timestamp - time();
    }

    /**
     * @return array
     */
    public function cacheControlHeaders(): array
    {
        if ($this->isConfidential()) {
            return [
                'Cache-Control' => 'max-age=' . $this->timeToExpireInSeconds() . ', public',
            ];
        }

        return [
            'Cache-Control' => 'max-age=31557600, public',
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function toPayload(): array
    {
        $data = [
            'p' => $this->filePath,
            's' => $this->size,
        ];

        // Don't add diskname if not necessary
        if ($this->diskName !== 'local') {
            $data['d'] = $this->diskName;
        }

        if (isset($this->expires)) {
            $data['e'] = $this->expires->timestamp;
        }

        return $data;
    }

}