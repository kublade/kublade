<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Class TemplateException.
 *
 * This class is the exception for template.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateException extends Exception
{
    /**
     * Construct the exception.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message, $code, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
