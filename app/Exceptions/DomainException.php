<?php

namespace App\Exceptions;

use Exception;

class DomainException extends Exception
{
    /**
     * Domain exceptions represent failures in the business logic rules.
     * 
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
