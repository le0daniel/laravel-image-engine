<?php

namespace le0daniel\Laravel\ImageEngine\Utility;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use le0daniel\Laravel\ImageEngine\Image\ImageException;
use le0daniel\Laravel\ImageEngine\Image\ImageRepresentation;
use League\Flysystem\Adapter\Local;

final class Images
{
    public const ORIGINAL_SIZE = 'original';

    static protected $imageExtensions = [
        'png',
        'jpg',
        'jpeg',
    ];

    public static function verifyImageExtension(string $requestedFormat)
    {
        if (!in_array($requestedFormat, self::$imageExtensions, true)) {
            throw ImageException::withHint(
                'Invalid image extension given.',
                "Available extensions: " . implode(', ', self::$imageExtensions)
            );
        }
    }

    public static function isOriginalSize(string $size): bool
    {
        return self::ORIGINAL_SIZE === $size;
    }

    private static function isLocalDisk(Filesystem $disk): bool
    {
        return $disk->getDriver()->getAdapter() instanceof Local;
    }

    public static function downloadForLocalProcessing(ImageRepresentation $imageRepresentation): string
    {
        $disk = $imageRepresentation->disk();

        // Return path from local disk
        if (self::isLocalDisk($disk)) {
            $localPathPrefix = rtrim($disk->getDriver()->getAdapter()->getPathPrefix(), '/');
            return realpath("{$localPathPrefix}/{$imageRepresentation->filePath}");
        }

        $baseCachePath = config('image-engine.original_cache_dir');
        [$folder, $name] = Strings::splitAtIndex(md5($imageRepresentation->filePath), 6);
        $cacheFilePath = "{$baseCachePath}/{$folder}/{$name}.{$imageRepresentation->extension}";

        Directories::makeRecursive(dirname($cacheFilePath));

        // Download and cache the file
        if (!file_exists($cacheFilePath)) {
            Resources::auto(
                fn($resource) => $disk->writeStream($imageRepresentation->filePath, $resource),
                'w+',
                $cacheFilePath
            );
        }

        return realpath($cacheFilePath);
    }

}
