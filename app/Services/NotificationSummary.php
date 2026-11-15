<?php

namespace App\Services;

class NotificationSummary
{
    private int $messages = 0;
    private int $expenses = 0;

    /**
     * @var array<int, array{channel: string, reason: string}>
     */
    private array $failures = [];

    public function addSent(string $channel, int $expenses): void
    {
        $this->messages++;
        $this->expenses += $expenses;
    }

    public function addFailure(string $channel, string $reason): void
    {
        $this->failures[] = ['channel' => $channel, 'reason' => $reason];
    }

    public function messages(): int
    {
        return $this->messages;
    }

    public function expenses(): int
    {
        return $this->expenses;
    }

    /**
     * @return array<int, array{channel: string, reason: string}>
     */
    public function failures(): array
    {
        return $this->failures;
    }

    public function hasFailures(): bool
    {
        return $this->failures !== [];
    }
}
