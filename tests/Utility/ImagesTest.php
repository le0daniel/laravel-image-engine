<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Utility;

use Illuminate\Filesystem\FilesystemAdapter;
use le0daniel\Laravel\ImageEngine\Image\ImageException;
use le0daniel\Laravel\ImageEngine\Image\ImageRepresentation;
use le0daniel\Laravel\ImageEngine\Utility\Images;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;

final class ImagesTest extends TestCase
{
    /** @dataProvider verifyImageExtensionDataProvider */
    public function testVerifyImageExtension(bool $expected, string ...$extensions): void
    {
        foreach ($extensions as $extension) {
            try {
                Images::verifyImageExtension($extension);
                $this->assertTrue($expected);
            } catch (ImageException $exception) {
                $this->assertFalse($expected);
            }
        }
    }

    public function verifyImageExtensionDataProvider(): array
    {
        return [
            'valid' => [true, 'jpg', 'jpeg', 'png'],
            'invalid' => [false, 'tif', 'tiff', '', 'other', 'random', 'tmp'],
        ];
    }

    public function testIsOriginalSize()
    {
        $this->assertTrue(Images::isOriginalSize('original'));
        $this->assertFalse(Images::isOriginalSize('original.'));
    }
}

