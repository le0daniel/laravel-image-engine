<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:37
 */

return [

    /**
     * Key used to sign images
     */
    'key' => env('APP_KEY'),

    /**
     * Available image sizes.
     * Consists of name => [ (int|null) width, (int|null) height, (bool|null) fill = false]
     */
    'sizes' => [
        'thumbnail' => [
            100, // Width
            100, // Height
            true // If true (height & width required!), it will fill the rectangle instead of just fitting inside
        ],
        'small' => [300, 300],
        'medium' => [600, null],
        'large' => [1200, 1200],
    ],

    /**
     * Directories the filesystem:clear command should clear
     *
     * absolute_dir => (array) config[
     *     seconds, hours, days, months => int, one must be present
     *     (optional) name => regex for filename (signature length)
     * ]
     */
    'dirs_to_clear' => [
        public_path('img/*/') => [
            'days' => 1,
            'name' => '/\.(jpg|png|jpeg)$/'
        ],
        storage_path('app/images/*/') => [
            'days' => 1,
            'name' => '/\.(jpg|png|jpeg)$/',
        ]
    ],

    /**
     * Path where the rendered confidential images are stored. All other images are
     * stored by default into public/img/{$payload}/{$hash}.{$extension}
     * This ensures, that local images are served by NGINX or Apache without requiring
     * the application to boot and prevent overhead.
     */
    'storage' => storage_path('app/images'),

    /**
     * Image manipulating requires images to be available on the disk
     * This images stored in s3 (or other non local accessible disk) needs to be downloaded.
     * To prevent loading the image every time a new size of the given image is
     * required, the original images are downloaded and stored locally
     *
     * Important, it's your job to clear that directory from time to time.
     */
    'original_cache_dir' => storage_path('app/original-images'),

    /**
     * Path used to create tmp files.
     */
    'tmp_directory' => storage_path('tmp'),

];
