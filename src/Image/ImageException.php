<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:56
 */

namespace le0daniel\Laravel\ImageEngine\Image;


use Throwable;

class ImageException extends \Exception
{
    protected $hint;

    public function __construct(string $message = "", string $hint = '', int $code = 0, Throwable $previous = null)
    {
        $this->hint = $hint;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param string $message
     * @param string $hint
     * @return ImageException
     */
    public static function withHint(string $message, string $hint){
        return new self($message, $hint);
    }

    /**
     * @return string
     */
    public function getHint(): string
    {
        return $this->hint;
    }

}