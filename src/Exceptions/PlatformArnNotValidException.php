<?php
namespace Imageplus\Sns\Exceptions;

use Exception;

class PlatformArnNotValidException extends Exception
{
    public function __construct($message, $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
