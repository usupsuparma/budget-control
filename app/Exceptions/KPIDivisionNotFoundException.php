<?php

namespace App\Exceptions;

class KPIDivisionNotFoundException extends DomainException
{
    public function __construct(string $message = 'Data KPI Division tidak ditemukan.')
    {
        parent::__construct($message, 404);
    }
}
