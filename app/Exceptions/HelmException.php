<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Class HelmException.
 *
 * This class is the exception for helm.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class HelmException extends Exception
{
    /**
     * Construct the exception.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message, $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
