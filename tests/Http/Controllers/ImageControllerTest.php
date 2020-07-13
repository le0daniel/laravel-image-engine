<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Intervention\Image\ImageManager;
use le0daniel\Laravel\ImageEngine\Http\Controllers\ImageController;
use le0daniel\Laravel\ImageEngine\Image\ImageEngine;
use le0daniel\Laravel\ImageEngine\Image\ImageException;
use le0daniel\Laravel\ImageEngine\Image\ImageRepresentation;
use le0daniel\Laravel\ImageEngine\Utility\SignatureException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ImageControllerTest extends TestCase
{
    use ProphecyTrait;

    private const SECRET = 'asdfasdf!?';
    private $imageEngine;
    private ImageController $imageController;

    protected function setUp(): void
    {
        $this->imageEngine = $this->prophesize(ImageEngine::class);
        $this->imageController = new ImageController(
            $this->imageEngine->reveal()
        );

        $this
            ->imageEngine
            ->render(Argument::type(ImageRepresentation::class), 'jpg', false)
            ->willReturn(test_files('image.jpg'))
        ;
    }

    public function testImage()
    {
        $this->imageEngine->getImageFromSignedString('folder::string')->willReturn(
            ImageRepresentation::from('path', 'medium')
        );

        /** @var BinaryFileResponse $binaryFileResponse */
        $binaryFileResponse = $this->imageController->image('folder', 'string', 'jpg');
        $this->assertSame(
            test_files('image.jpg'),
            $binaryFileResponse->getFile()->getRealPath()
        );
    }

    public function testExpired()
    {
        $this->imageEngine->getImageFromSignedString('folder::string')->willReturn(
            ImageRepresentation::from('path', 'medium', Carbon::now()->subHour())
        );

        /** @var JsonResponse $jsonResponse */
        $jsonResponse = $this->imageController->image('folder', 'string', 'jpg');
        $this->assertSame(
            410,
            $jsonResponse->getStatusCode()
        );
    }

    public function testInvalidSignature()
    {
        $this->imageEngine->getImageFromSignedString('folder::string')->willThrow(
            new SignatureException('invalid sig')
        );

        /** @var JsonResponse $jsonResponse */
        $jsonResponse = $this->imageController->image('folder', 'string', 'jpg');
        $this->assertSame(
            422,
            $jsonResponse->getStatusCode()
        );
    }

    public function testImageExceptionHandling()
    {
        $this->imageEngine->getImageFromSignedString('folder::string')->willThrow(
            ImageException::withHint('test', 'value')
        );

        /** @var JsonResponse $jsonResponse */
        $jsonResponse = $this->imageController->image('folder', 'string', 'jpg');
        $this->assertSame(
            500,
            $jsonResponse->getStatusCode()
        );
    }
}
