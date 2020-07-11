<?php

namespace le0daniel\Laravel\ImageEngine\Contract;

use Intervention\Image\Image;

abstract class ImageManipulator
{
    abstract public function handle(Image $image): void;
}
