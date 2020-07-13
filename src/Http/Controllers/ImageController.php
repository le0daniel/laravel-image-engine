<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:42
 */

namespace le0daniel\Laravel\ImageEngine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use le0daniel\Laravel\ImageEngine\Image\ImageException;
use le0daniel\Laravel\ImageEngine\Image\ImageEngine;
use le0daniel\Laravel\ImageEngine\Utility\SignatureException;
use le0daniel\Laravel\ImageEngine\Utility\Signatures;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ImageController extends BaseController
{
    private ImageEngine $imageEngine;

    public function __construct(ImageEngine $imageEngine)
    {
        $this->imageEngine = $imageEngine;
    }

    private function jsonResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }

    private function expired(): JsonResponse
    {
        return $this->jsonResponse(['error' => 'Expired',], 410);
    }

    public function image(string $folder, string $path, string $extension)
    {
        try {
            $imageRepresentation = $this->imageEngine->getImageFromSignedString(
                $folder . Signatures::SIGNATURE_STRING_SEPARATOR . $path
            );
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
            return $this->jsonResponse(['error' => 'Invalid signature provided'], 422);
        } catch (ImageException $error) {
            return $this->jsonResponse(['error' => 'Internal rendering error'], 500);
        }
    }

}
