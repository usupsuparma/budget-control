<?php

namespace App\Exceptions;

class KPIDepartmentNotFoundException extends DomainException
{
    public function __construct(string $message = 'Data KPI Department tidak ditemukan.')
    {
        parent::__construct($message, 404);
    }
}
