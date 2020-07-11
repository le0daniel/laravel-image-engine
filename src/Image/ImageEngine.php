<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:54
 */

namespace le0daniel\Laravel\ImageEngine\Image;

use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;
use le0daniel\Laravel\ImageEngine\Utility\Directories;
use le0daniel\Laravel\ImageEngine\Utility\Images;
use le0daniel\Laravel\ImageEngine\Utility\Signatures;
use le0daniel\Laravel\ImageEngine\Utility\Strings;

final class ImageEngine
{
    private const IMAGE_RESIZE_DIMENSIONS_TO_FIT = 3000;

    private array $sizes;
    private array $filters = [];
    private string $secret;
    private string $tmpPath;
    private ImageManager $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
        $this->sizes = config('image-engine.sizes');
        $this->secret = config('image-engine.key');
        $this->tmpPath = rtrim(config('image-engine.tmp_directory'), '/');
    }

    private function getBasePath(ImageRepresentation $imageRepresentation, bool $forceConfidential): string
    {
        $basePath = $imageRepresentation->isConfidential || $forceConfidential
            ? config('image-engine.storage')
            : public_path('img');

        return rtrim($basePath, '/');
    }

    /**
     * @param string $size
     * @return array
     * @throws ImageException
     */
    private function getSizeArray(string $size): array
    {
        if (!array_key_exists($size, $this->sizes)) {
            throw ImageException::withHint(
                'Invalid size.',
                "Given {$size}, available: " . implode(', ', array_keys($this->sizes))
            );
        }

        return [
            $this->sizes[$size][0],
            $this->sizes[$size][1],
            $this->sizes[$size][2] ?? false,
        ];
    }

    private function applyFilters(\Intervention\Image\Image $image, string $size)
    {
        if (!array_key_exists($size, $this->filters)) {
            return;
        }

        /* Loop through filters */
        foreach ($this->filters[$size] as $filter => $params) {
            /* No params given */
            if (is_numeric($filter)) {
                $filter = $params;
                $params = [];
            }

            /* Cast single params as array */
            if (!is_array($params)) {
                $params = [$params];
            }

            /* Apply filter */
            $image->{$filter}(...$params);
        }
    }

    private function getAbsoluteRenderPath(
        ImageRepresentation $imageRepresentation,
        string $extension,
        bool $forceConfidential
    ): string {
        $basePath = $this->getBasePath($imageRepresentation, $forceConfidential);
        [$folder, $path] = $this->serializeImage($imageRepresentation);
        return "{$basePath}/{$folder}/{$path}.{$extension}";
    }

    private function getTmpPath(string $extension)
    {
        $randomName = Str::random(20);
        return "{$this->tmpPath}/{$randomName}.{$extension}";
    }

    public function serializeImage(ImageRepresentation $imageRepresentation): array
    {
        $signature = Signatures::sign($this->secret, $imageRepresentation->serialize());
        return Strings::splitAtIndex($signature, 20);
    }

    public function getImageSignedString(string $signedString): ImageRepresentation
    {
        $payload = Signatures::verifyAndReturnPayloadString($this->secret, $signedString);
        return ImageRepresentation::fromSerialized($payload);
    }

    public function render(
        ImageRepresentation $imageRepresentation,
        string $extension,
        bool $forceConfidential = false
    ): string {
        Images::verifyImageExtension($extension);

        $absoluteRenderPath = $this->getAbsoluteRenderPath(
            $imageRepresentation,
            $extension,
            $forceConfidential
        );

        if (file_exists($absoluteRenderPath)) {
            return $absoluteRenderPath;
        }

        if (!file_exists(dirname($absoluteRenderPath))) {
            Directories::makeRecursive(dirname($absoluteRenderPath));
        }

        // Original requested
        if (Images::isOriginalSize($imageRepresentation->size)) {
            copy(Images::realPath($imageRepresentation), $absoluteRenderPath);
            return $absoluteRenderPath;
        }

        [$x, $y, $fit] = $this->getSizeArray($imageRepresentation->size);

        $image = $this
            ->imageManager
            ->make(Images::realPath($imageRepresentation));

        /* Resize the image */
        if ($fit) {
            $image->fit(
                $x,
                $y,
                static function (Constraint $constraint) {
                    $constraint->upsize();
                }
            );
        } else {
            $image->resize(
                $x,
                $y,
                static function (Constraint $constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            );
        }

        /* Add Filters */
        $this->applyFilters($image, $imageRepresentation->size);

        /* Save */
        $image->save($absoluteRenderPath);

        /* Free Memory and return */
        unset($image);
        return $absoluteRenderPath;
    }

    public function convertUploadedImage(
        string $absoluteImagePath,
        string $extension,
        \Closure $callback,
        int $imageDimensions = self::IMAGE_RESIZE_DIMENSIONS_TO_FIT
    ): void {
        Images::verifyImageExtension($extension);
        try {
            $tmpFileName = $this->getTmpPath($extension);

            $image = $this->imageManager->make($absoluteImagePath);
            $image->resize(
                $imageDimensions,
                $imageDimensions,
                static function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            );
            $image->save($tmpFileName);
            unset($image);

            $callback($tmpFileName);
        } catch (\Exception $exception) {
            if (file_exists($tmpFileName)) {
                unlink($tmpFileName);
            }
            throw $exception;
        }

        unlink($tmpFileName);
    }

}
