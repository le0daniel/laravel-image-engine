<?php

namespace le0daniel\Tests\Laravel\ImageEngine\Image\Manipulator;

use Intervention\Image\Image;
use le0daniel\Laravel\ImageEngine\Image\Manipulator\Fit;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

final class FitTest extends TestCase
{
    use ProphecyTrait;

    public function testHandle()
    {
        $image = $this->prophesize(Image::class);
        $image->fit(10, 20, Argument::type(\Closure::class))->shouldBeCalled();
        (new Fit(10, 20))->handle($image->reveal());
    }
}
