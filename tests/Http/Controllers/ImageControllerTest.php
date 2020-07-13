<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Http\Controllers;

use Intervention\Image\ImageManager;
use le0daniel\Laravel\ImageEngine\Http\Controllers\ImageController;
use le0daniel\Laravel\ImageEngine\Image\ImageEngine;
use le0daniel\Laravel\ImageEngine\Image\ImageRepresentation;
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
    }

    public function testImage()
    {
        $this->imageEngine->getImageFromSignedString('folder::string')->willReturn(
            ImageRepresentation::from('path', 'medium')
        );

        $this
            ->imageEngine
            ->render(Argument::type(ImageRepresentation::class), 'jpg', false)
            ->willReturn(test_files('image.jpg'))
        ;

        /** @var BinaryFileResponse $binaryFileResponse */
        $binaryFileResponse = $this->imageController->image('folder', 'string', 'jpg');
        $this->assertSame(
            test_files('image.jpg'),
            $binaryFileResponse->getFile()->getRealPath()
        );
    }
}
