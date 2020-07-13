<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Utility;

use le0daniel\Laravel\ImageEngine\Utility\SignatureException;
use le0daniel\Laravel\ImageEngine\Utility\Signatures;
use PHPUnit\Framework\TestCase;

final class SignaturesTest extends TestCase
{
    private const SECRET = 'asdfasdf!_';

    public function testSign()
    {
        $this->assertSame(
            'the-string::PT7sqFr2CdpljWjc5HCbLevrooH_xT1D-WDnLBEBqzI',
            Signatures::sign(self::SECRET, 'the-string')
        );
    }

    public function testVerifyAndReturnPayloadString()
    {
        $this->assertEquals(
            'the-string',
            Signatures::verifyAndReturnPayloadString(
                self::SECRET,
                'the-string::PT7sqFr2CdpljWjc5HCbLevrooH_xT1D-WDnLBEBqzI'
            )
        );
    }

    public function testVerifyAndReturnPayloadStringWithInvalidStructure()
    {
        $this->expectException(SignatureException::class);
        $this->expectExceptionMessage('The signed string is of invalid structure');
        Signatures::verifyAndReturnPayloadString(self::SECRET, 'asdadasd');

        $this->fail('Should not reach this.');
    }

    public function testVerifyAndReturnPayloadStringWithTamperedString()
    {
        $this->expectException(SignatureException::class);
        $this->expectExceptionMessage('Signature mismatches.');
        Signatures::verifyAndReturnPayloadString(self::SECRET, 'asdadasd::ajbflenfiwenfiwef');

        $this->fail('Should not reach this.');
    }
}
