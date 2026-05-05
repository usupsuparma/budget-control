<?php

namespace App\Services\PipIntegrationService\DTOs;

/**
 * Data Transfer Object for Pengeluaran Reguler submission response from PIP.
 */
readonly class PengeluaranRegulerResponseData
{
    public function __construct(
        public bool    $success,
        public string  $message,
        public array   $data = [],
    ) {}

    public static function fromArray(array $response): self
    {
        return new self(
            success: (bool) ($response['success'] ?? false),
            message: (string) ($response['message'] ?? ''),
            data:    (array)  ($response['data'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data'    => $this->data,
        ];
    }
}
