<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class FluxException extends Exception
{
    public function __construct($message, $code, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
