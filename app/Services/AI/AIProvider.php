<?php
namespace App\Services\AI;

interface AIProvider
{
    /** @return array<string, mixed> */
    public function analyze(string $message, array $orderContext): array;
}
