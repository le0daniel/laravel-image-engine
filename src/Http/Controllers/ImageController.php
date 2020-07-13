<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:42
 */

namespace le0daniel\Laravel\ImageEngine\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use le0daniel\Laravel\ImageEngine\Image\ImageException;
use le0daniel\Laravel\ImageEngine\Image\ImageEngine;
use le0daniel\Laravel\ImageEngine\Utility\SignatureException;
use le0daniel\Laravel\ImageEngine\Utility\Signatures;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageController extends BaseController
{
    private ImageEngine $imageEngine;

    public function __construct(ImageEngine $imageEngine)
    {
        $this->imageEngine = $imageEngine;
    }

    private function expired()
    {
        return response()->json(
            [
                'error' => 'Expired'
            ],
            410
        );
    }

    public function image(string $folder, string $path, string $extension)
    {
        try {
            $imageRepresentation = $this->imageEngine->getImageFromSignedString($folder . Signatures::SIGNATURE_STRING_SEPARATOR . $path);
            if ($imageRepresentation->isExpired) {
                return $this->expired();
            }

            $absoluteImagePath = $this->imageEngine->render(
                $imageRepresentation,
                $extension,
                false
            );

            return new BinaryFileResponse(
                new \SplFileInfo($absoluteImagePath),
                200,
                $imageRepresentation->cacheControlHeaders()
            );
        } catch (SignatureException $signatureException) {
            Log::error('Image Rendering: ' . $signatureException->getMessage());
            return response()->json(
                [
                    'Error' => 'Invalid signature provided',
                ],
                422
            );
        } catch (ImageException $error) {
            Log::error('Image Rendering: ' . $error->getMessage() . ' => ' . $error->getHint());
            return response()->json(
                [
                    'Error' => 'Internal rendering error',
                ],
                500
            );
        }
    }

}
