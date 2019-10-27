<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 13:41
 */

if (!function_exists('image_url')) {

    /**
     * Returns the signed url of an image.
     * Important, this does not render the image.
     *
     * @param \le0daniel\Laravel\ImageEngine\Image\Image $image
     * @param string $extension
     * @return string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException|Exception
     */
    function image_url(\le0daniel\Laravel\ImageEngine\Image\Image $image, string $extension): string
    {
        $signer = app()->make(\le0daniel\Laravel\ImageEngine\Image\Signer::class);
        list($payload, $signature) = $signer->signPayload($image->toPayload());

        return \Illuminate\Support\Facades\URL::route('image-engine.image', [
            'payload' => $payload,
            'signature' => $signature,
            'extension' => $extension
        ]);
    }
}

if (!function_exists('image_real_path')) {

    /**
     * Render an image and return it's local path on disk
     *
     * @param \le0daniel\Laravel\ImageEngine\Image\Image $image
     * @param string $extension
     * @param bool $confidential
     * @return string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \le0daniel\Laravel\ImageEngine\Image\ImageException
     */
    function image_real_path(\le0daniel\Laravel\ImageEngine\Image\Image $image, string $extension, bool $confidential = true): string
    {
        /** @var \le0daniel\Laravel\ImageEngine\Image\Renderer $renderer */
        $renderer = app()->make(\le0daniel\Laravel\ImageEngine\Image\Renderer::class);

        return $renderer->render(
            $image, $extension, $confidential
        );
    }
}