<?php
namespace Imageplus\Sns\Exceptions;

use InvalidArgumentException;

class InvalidCallbackException extends InvalidArgumentException
{
    public function __construct($message, $code = 0, \Throwable $previous = null) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}
