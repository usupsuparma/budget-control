<?php

namespace App\Services\PipIntegrationService\DTOs;

/**
 * Generic DTO for PIP API list/GET endpoint responses.
 *
 * Wraps the standard envelope: { success, message, data[], total }
 */
readonly class PipApiResponse
{
    public function __construct(
        public bool   $success,
        public string $message,
        public array  $data = [],
        public int    $total = 0,
    ) {}

    public static function fromArray(array $response): self
    {
        return new self(
            success: (bool)   ($response['success'] ?? false),
            message: (string) ($response['message'] ?? ''),
            data:    (array)  ($response['data']    ?? []),
            total:   (int)    ($response['total']   ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data'    => $this->data,
            'total'   => $this->total,
        ];
    }
}
