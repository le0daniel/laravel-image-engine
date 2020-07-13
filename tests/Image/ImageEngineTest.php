<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Image;

use Intervention\Image\ImageManager;
use le0daniel\Laravel\ImageEngine\Image\ImageEngine;
use le0daniel\Laravel\ImageEngine\Image\ImageRepresentation;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class ImageEngineTest extends TestCase
{
    use ProphecyTrait;
    private const SECRET = 'asdfasdf!?';
    private $imageManager;
    private ImageEngine $imageEngine;

    protected function setUp(): void
    {
        $this->imageManager = $this->prophesize(ImageManager::class);
        $this->imageEngine = new ImageEngine(
            $this->imageManager->reveal(),
            self::SECRET,
            test_files('')
        );
    }

    public function testSerializeAndSignImage()
    {
        [$serialized, $signature] = $this->imageEngine->serializeAndSignImage(ImageRepresentation::from(
            'test',
            'medium'
        ));
        $this->assertEquals('eyJzIjoibWVkaXVtIiwicCI6InRlc3QifQ', $serialized);
        $this->assertEquals('P6ZVsi5HxRKd412m87AITMlde88iOjc76YIAOBpvP4o', $signature);
    }

    public function testGetImageFromSignedString()
    {
        $image = $this->imageEngine->getImageFromSignedString(
            'eyJzIjoibWVkaXVtIiwicCI6InRlc3QifQ::P6ZVsi5HxRKd412m87AITMlde88iOjc76YIAOBpvP4o'
        );
        $this->assertEquals('test', $image->filePath);
        $this->assertEquals('medium', $image->size);
        $this->assertEquals(ImageRepresentation::DEFAULT_DISK_NAME, $image->diskName);
        $this->assertFalse($image->isConfidential);
    }
}
