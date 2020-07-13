<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Utility;

use le0daniel\Laravel\ImageEngine\Utility\Json;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{

    public function testEncode()
    {
        $this->assertEquals('{"test":1}', Json::encode(['test' => 1]));
    }

    public function testDecode()
    {
        $this->assertEquals(['test' => 1, 'default' => 1], Json::decode('{"test":1}', ['default' => 1]));

        $this->expectException(\Exception::class);
        Json::decode('{1}');
    }
}
