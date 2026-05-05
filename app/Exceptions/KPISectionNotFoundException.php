<?php

namespace App\Exceptions;

class KPISectionNotFoundException extends DomainException
{
    public function __construct(string $message = 'Data KPI Section tidak ditemukan.')
    {
        parent::__construct($message, 404);
    }
}
