<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:42
 */

namespace le0daniel\Laravel\ImageEngine\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use le0daniel\Laravel\ImageEngine\Image\Image;
use le0daniel\Laravel\ImageEngine\Image\ImageException;
use le0daniel\Laravel\ImageEngine\Image\Renderer;
use le0daniel\Laravel\ImageEngine\Image\Signer;

class ImageController extends BaseController
{
    /**
     * @var Signer
     */
    protected $signer;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * ImageController constructor.
     * @param Signer $signer
     * @param Renderer $renderer
     */
    public function __construct(Signer $signer, Renderer $renderer)
    {
        $this->signer = $signer;
        $this->renderer = $renderer;
    }


    /**
     * @param string $unverifiedPayload
     * @param string $signature
     * @param string $extension
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception
     */
    public function image(string $unverifiedPayload, string $signature, string $extension)
    {
        try {
            $payload = $this->signer->verifyAndUnpack($unverifiedPayload, $signature);
        } catch (\Exception $error) {
            return response()->json([
                'error' => 'Invalid signature.'
            ], 403);
        }

        $image = Image::createFromPayload($payload);

        // Check if expired
        if ($image->isExpired()) {
            response()->json([
                'error' => 'Expired'
            ], 403);
        }

        try {
            return response()->file(
                $this->renderer->render($image, $extension),
                $image->cacheControlHeaders()
            );
        } catch (ImageException $exception) {
            if (config('app.debug') === true) {
                return response()->json([
                    'error' => $exception->getMessage(),
                    'hint' => $exception->getHint(),
                ], 500);
            }
            return response()->json([
                'error' => 'Invalid request. Forbidden'
            ], 403);
        }
    }

}