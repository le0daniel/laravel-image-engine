<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 13:41
 */

use Illuminate\Support\Facades\URL;
use le0daniel\Laravel\ImageEngine\Image\ImageEngine;
use le0daniel\Laravel\ImageEngine\Image\ImageRepresentation;

if (!function_exists('image_url')) {
    function image_url(ImageRepresentation $image, string $extension): string
    {
        [$folder, $path] = app()->make(ImageEngine::class)->serializeImage($image);
        return URL::route(
            'image-engine.image',
            [
                'folder' => $folder,
                'path' => $path,
                'extension' => $extension,
            ]
        );
    }
}

if (!function_exists('image_real_path')) {
    function image_real_path(ImageRepresentation $image, string $extension, bool $confidential = true): string
    {
        return app()->make(ImageEngine::class)->render(
            $image,
            $extension,
            $confidential
        );
    }
}
