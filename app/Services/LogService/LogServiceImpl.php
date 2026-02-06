<?php

namespace App\Services\LogService;

use Illuminate\Support\Facades\Log;

class LogServiceImpl implements LogService
{
    /**
     * Constructor with dependencies.
     */
    public function __construct()
    {
        // Inject your dependencies here
    }

    // Implement your interface methods here
    // public function someMethod()
    // {
    //     //
    // }

    public function create(string $message, array $context = [], string $level = 'info')
    {
        if ($level == 'info') {
            Log::info($message, $context);
        } elseif ($level == 'warning') {
            Log::warning($message, $context);
        } elseif ($level == 'error') {
            Log::error($message, $context);
        } elseif ($level == 'debug') {
            Log::debug($message, $context);
        } elseif ($level == 'notice') {
            Log::notice($message, $context);
        } elseif ($level == 'critical') {
            Log::critical($message, $context);
        } elseif ($level == 'alert') {
            Log::alert($message, $context);
        } elseif ($level == 'emergency') {
            Log::emergency($message, $context);
        }
    }
}
