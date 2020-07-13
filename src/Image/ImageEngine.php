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
use le0daniel\Laravel\ImageEngine\Contract\ImageManipulator;
use le0daniel\Laravel\ImageEngine\Image\Manipulator\Fit;
use le0daniel\Laravel\ImageEngine\Image\Manipulator\Resize;
use le0daniel\Laravel\ImageEngine\Utility\Directories;
use le0daniel\Laravel\ImageEngine\Utility\Images;
use le0daniel\Laravel\ImageEngine\Utility\Signatures;
use le0daniel\Laravel\ImageEngine\Utility\Strings;

final class ImageEngine
{
    private const IMAGE_RESIZE_DIMENSIONS_TO_FIT = 3000;

    private string $secret;
    private string $tmpPath;
    private ImageManager $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
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

    private function getConfiguration(string $size): array
    {
        $configuration = config('image-engine.sizes', [])[$size] ?? null;
        if (!$configuration) {
            throw ImageException::withHint("Invalid size given: {$size}", 'Please provide a configured size.');
        }

        return [
            $configuration[0],
            $configuration[1],
            $configuration[2] ?? false,
            $configuration['manipulation'] ?? null,
        ];
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
        return explode('::', $signature, 2);
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
        Directories::makeRecursive(dirname($absoluteRenderPath));

        // Original requested
        if (Images::isOriginalSize($imageRepresentation->size)) {
            copy(Images::downloadForLocalProcessing($imageRepresentation), $absoluteRenderPath);
            return $absoluteRenderPath;
        }

        [$x, $y, $fit, $userProvidedManipulators] = $this->getConfiguration($imageRepresentation->size);

        $image = $this->imageManager->make(Images::downloadForLocalProcessing($imageRepresentation));

        $manipulators = [
            $fit ? new Fit($x, $y) : new Resize($x, $y),
        ];
        if ($userProvidedManipulators) {
            foreach ($userProvidedManipulators as $manipulator) {
                $manipulators[] = new $manipulator;
            }
        }

        array_walk($manipulators, fn(ImageManipulator $manipulator) => $manipulator->handle($image));

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
            if (isset($tmpFileName) && file_exists($tmpFileName)) {
                unlink($tmpFileName);
            }
            throw $exception;
        }

        unlink($tmpFileName);
    }

}
