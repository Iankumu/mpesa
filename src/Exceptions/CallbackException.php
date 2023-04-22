<?php

namespace Iankumu\Mpesa\Exceptions;

use Exception;

class CallbackException extends Exception
{
    public static function make(string $callback, string $message = null): self
    {
        if (! is_null($message)) {
            return new self("The {$callback} cannot be null. ".$message);
        } else {
            return new self("The {$callback} cannot be null.");
        }
    }
}
