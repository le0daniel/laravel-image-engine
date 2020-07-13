<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Utility;

use le0daniel\Laravel\ImageEngine\Utility\Base64;
use PHPUnit\Framework\TestCase;

final class Base64Test extends TestCase
{

    public function testUrlEncode()
    {
        $this->assertSame(
            'e1lYTmtaJMOkwqjCqGx-flvOqWdhYXNmd2ZfcyFmX8Kowqg1OHVoZXdvZjI',
            Base64::urlEncode('{YXNkZ$ä¨¨l~~[Ωgaasfwf_s!f_¨¨58uhewof2')
        );
    }

    public function testDecode()
    {
        $this->assertSame('asdf', Base64::decode('YXNkZg=='));
    }

    public function testUrlDecode()
    {
        $this->assertSame(
            'asdf',
            Base64::urlDecode('YXNkZg')
        );
    }

    public function testEncode()
    {
        $this->assertSame('YXNkZg==', Base64::encode('asdf'));
    }
}
