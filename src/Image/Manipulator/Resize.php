<?php

namespace le0daniel\Laravel\ImageEngine\Image\Manipulator;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use le0daniel\Laravel\ImageEngine\Contract\ImageManipulator;

final class Resize extends ImageManipulator
{

    private ?int $x;
    private ?int $y;

    public function __construct(?int $x, ?int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function handle(Image $image): void
    {
        $image->resize(
            $this->x,
            $this->y,
            static function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            }
        );
    }
}
