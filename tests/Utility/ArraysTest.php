<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Utility;

use le0daniel\Laravel\ImageEngine\Utility\Arrays;
use PHPUnit\Framework\TestCase;

final class ArraysTest extends TestCase
{

    public function testRemoveNullValues()
    {
        $this->assertSame(['key' => 'value'], Arrays::removeNullValues(['key' => 'value', 0 => null]));
        $this->assertSame([], Arrays::removeNullValues(['key' => null]));
        $this->assertSame([], Arrays::removeNullValues([null]));
        $this->assertSame([0], Arrays::removeNullValues([0]));
        $this->assertSame([''], Arrays::removeNullValues(['']));
        $this->assertSame([false], Arrays::removeNullValues([false]));
    }

    public function testMapKeys()
    {
        $this->assertSame(
            [
                'key' => 'value',
                0 => 'otherVal',
            ],
            Arrays::mapKeys(
                [
                    'value',
                    'somekey' => 'otherVal',
                ],
                [
                    0 => 'key',
                    'somekey' => 0,
                ]
            )
        );
    }

    public function testApplyDefaultValues()
    {
        $this->assertEquals(
            [
                'key' => 'value',
                'default' => null,
            ],
            Arrays::applyDefaultValues(
                [
                    'key' => 'value',
                ],
                [
                    'key' => 'myDefaultKey',
                    'default' => null,
                ]
            )
        );
    }
}
