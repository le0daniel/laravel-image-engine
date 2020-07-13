<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Utility;

use le0daniel\Laravel\ImageEngine\Utility\Strings;
use PHPUnit\Framework\TestCase;

final class StringsTest extends TestCase
{

    public function testSplitAtIndex()
    {
        $this->assertEquals(['test', '-string'], Strings::splitAtIndex('test-string', 4));
        $this->assertEquals(['', 'test-string'], Strings::splitAtIndex('test-string', 0));
        $this->assertEquals(['test-string', ''], Strings::splitAtIndex('test-string', 100));

        $this->assertEquals(['test-strin', 'g'], Strings::splitAtIndex('test-string', 10));
        $this->assertEquals(['test-string', ''], Strings::splitAtIndex('test-string', 11));
        $this->assertEquals(['test-string', ''], Strings::splitAtIndex('test-string', 12));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can not spilt a string at a negative index.');
        Strings::splitAtIndex('test-string', -1);

        $this->fail('Should not reach this.');
    }
}
