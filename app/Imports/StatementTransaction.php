<?php

namespace App\Imports;

use Carbon\CarbonImmutable;

final readonly class StatementTransaction
{
    public function __construct(
        public CarbonImmutable $occurredAt,
        public string $description,
        public string $amount,
        public TransactionDirection $direction,
        public ?string $externalId = null,
        public ?int $installmentNumber = null,
        public ?int $totalInstallments = null,
    ) {}

    public function isInstallment(): bool
    {
        return $this->installmentNumber !== null && $this->totalInstallments !== null;
    }

    public function signature(): string
    {
        if ($this->externalId !== null && $this->externalId !== '') {
            return hash('sha256', 'fitid:' . $this->externalId);
        }

        return hash('sha256', implode('|', [
            $this->occurredAt->format('Y-m-d'),
            mb_strtolower($this->description),
            $this->amount,
            $this->direction->value,
        ]));
    }
}
