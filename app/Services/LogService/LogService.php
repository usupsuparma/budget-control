<?php

namespace App\Services\LogService;

interface LogService
{
    // Define your service methods here
    // public function someMethod();

    public function create(string $message, array $context = [], string $level = 'info');
}
