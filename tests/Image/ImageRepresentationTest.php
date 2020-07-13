<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Image;

use Carbon\Carbon;
use le0daniel\Laravel\ImageEngine\Image\ImageRepresentation;
use PHPUnit\Framework\TestCase;

final class ImageRepresentationTest extends TestCase
{
    /** @dataProvider fromSerializedDataProvider */
    public function testFromSerialized($expectedSerialized, ImageRepresentation $image)
    {
        $serialized = $image->serialize();
        $this->assertSame($expectedSerialized, $serialized);
        $unserializedImage = ImageRepresentation::fromSerialized($serialized);

        foreach (['filePath', 'size', 'expires', 'diskName'] as $attribute) {
            $this->assertEquals(
                $image->{$attribute},
                $unserializedImage->{$attribute}
            );
        }
    }

    public function fromSerializedDataProvider()
    {
        return [
            'Simple image' => [
                'eyJwIjoiZmlsZSIsInMiOiJtZWRpdW0ifQ',
                ImageRepresentation::from('file', 'medium'),
            ],
            'Image with expiry' => [
                'eyJwIjoiZmlsZSIsInMiOiJtZWRpdW0iLCJlIjoxMDB9',
                ImageRepresentation::from('file', 'medium', 100),
            ],
            'Image with all attributes' => [
                'eyJkIjoiZGlzayIsInAiOiJmaWxlIiwicyI6Im1lZGl1bSIsImUiOjEwMH0',
                ImageRepresentation::from('file', 'medium', 100, 'disk'),
            ],
            'Image with carbon time' => [
                'eyJkIjoiZGlzayIsInAiOiJmaWxlIiwicyI6Im1lZGl1bSIsImUiOjEwMH0',
                ImageRepresentation::from('file', 'medium', Carbon::createFromTimestamp(100), 'disk'),
            ],
            'Image without expires' => [
                'eyJkIjoiZGlzayIsInAiOiJmaWxlIiwicyI6Im1lZGl1bSJ9',
                ImageRepresentation::from('file', 'medium', null, 'disk'),
            ],
        ];
    }

    public function testIsConfidential()
    {
        $this->assertTrue(ImageRepresentation::from('file', 'medium', 100, 'disk')->isConfidential);
        $this->assertFalse(ImageRepresentation::from('file', 'medium')->isConfidential);
    }

    public function testIsExpired()
    {
        $this->assertFalse(ImageRepresentation::from('file', 'medium', now()->addMinutes(100), 'disk')->isExpired);
        $this->assertFalse(ImageRepresentation::from('file', 'medium')->isExpired);
        $this->assertTrue(ImageRepresentation::from('file', 'medium', now()->subMinutes(100), 'disk')->isExpired);

    }

    public function testExpiresCarbon()
    {
        $this->assertInstanceOf(
            Carbon::class,
            ImageRepresentation::from('file', 'medium', 100, 'disk')->expiresCarbon
        );
        $this->assertNull(ImageRepresentation::from('file', 'medium')->expiresCarbon);
    }

    public function testTimestamp()
    {
        $this->assertEquals(100,ImageRepresentation::from('file', 'medium', 100, 'disk')->timestamp);
        $this->assertNull(ImageRepresentation::from('file', 'medium')->timestamp);
    }

    public function testExtensions()
    {
        $extensions = ['png', 'jpg', 'tiff', 'some'];
        foreach ($extensions as $extension) {
            $image = ImageRepresentation::from("file.$extension", 'medium');
            $this->assertEquals($extension, $image->extension);
        }
    }

    // public function testCacheControlHeaders()
    // {
    // }

    public function testFrom()
    {
        $this->assertInstanceOf(ImageRepresentation::class, ImageRepresentation::from('file', 'medium'));
    }

    // public function testToArray()
    // {
    // }
//
    // public function testSerialize()
    // {
    // }
}
