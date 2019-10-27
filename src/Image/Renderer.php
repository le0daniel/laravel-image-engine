<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:54
 */

namespace le0daniel\Laravel\ImageEngine\Image;

use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;
use le0daniel\Laravel\ImageEngine\Image\Image;
use le0daniel\Laravel\ImageEngine\Image\ImageException;
use le0daniel\Laravel\ImageEngine\Image\Signer;

class Renderer
{
    protected $sizes;

    protected $filters = [];

    /**
     * Extensions of the original image
     *
     * @var array
     */
    static protected $imageExtensions = [
        'png',
        'jpg',
        'jpeg'
    ];

    /** @var ImageManager */
    protected $imageManager;

    /** @var Signer */
    protected $signer;

    /**
     * Renderer constructor.
     * @param ImageManager $imageManager
     * @param Signer $signer
     */
    public function __construct(ImageManager $imageManager, Signer $signer)
    {
        $this->imageManager = $imageManager;
        $this->signer = $signer;
        $this->sizes = config('image-engine.sizes');
    }

    /**
     * @param string $requestedFormat
     * @throws ImageException
     */
    protected function validateFormat(string $requestedFormat)
    {
        if (!in_array($requestedFormat, self::$imageExtensions)) {
            throw ImageException::withHint(
                'Invalid extension',
                "Given .{$requestedFormat}, available " . implode(', ', self::$imageExtensions)
            );
        }
    }

    /**
     * @param Image $image
     * @return string
     */
    protected function getBasePath(Image $image, bool $confidential): string
    {
        if ($confidential || $image->isConfidential()) {
            return config('image-engine.storage');
        }

        return public_path('img');
    }

    /**
     * @param string $size
     * @return array
     * @throws ImageException
     */
    protected function getSizeArray(string $size): array
    {
        if (!array_key_exists($size, $this->sizes)) {
            throw ImageException::withHint(
                'Invalid size.',
                "Given {$size}, available: " . implode(', ', $this->sizes)
            );
        }

        return [
            $this->sizes[$size][0],
            $this->sizes[$size][1],
            $this->sizes[$size][2] ?? false
        ];
    }

    /**
     * @param Image $image
     */
    protected function applyFilters(\Intervention\Image\Image $image, string $size)
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

    /**
     * @param \le0daniel\Laravel\ImageEngine\Image\Image $image
     * @param string $format
     * @param bool $confidential
     * @return string
     * @throws \le0daniel\Laravel\ImageEngine\Image\ImageException|\Exception
     */
    public function render(Image $image, string $format, bool $confidential = false): string
    {
        $this->validateFormat($format);

        list($payload, $signature) = $this->signer->signPayload(
            $image->toPayload()
        );

        // Calculates the format
        $absoluteRenderPath = $this->getBasePath($image, $confidential) . '/' . $payload . '/' . $signature . '.' . $format;

        // Already rendered
        if (file_exists($absoluteRenderPath)) {
            return $absoluteRenderPath;
        }

        if (!file_exists(dirname($absoluteRenderPath))) {
            mkdir(dirname($absoluteRenderPath), 0777, true);
        }

        // Original requested
        if ($image->size() === 'original') {
            copy($image->realPath(), $absoluteRenderPath);
            return $absoluteRenderPath;
        }

        list($x, $y, $fit) = $this->getSizeArray($image->size());

        $interventionImage = $this->imageManager->make($image->realPath());

        /* Resize the image */
        if ($fit){
            $interventionImage->fit($x, $y, function (Constraint $constraint) {
                $constraint->upsize();
            });
        }
        else {
            $interventionImage->resize($x, $y, function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        /* Add Filters */
        $this->applyFilters($interventionImage, $image->size());

        /* Save */
        $interventionImage->save($absoluteRenderPath);

        /* Free Memory and return */
        unset($interventionImage);
        return $absoluteRenderPath;
    }

}