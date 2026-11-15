<?php

namespace App\Imports\Csv;

use App\Imports\DecimalAmount;
use App\Imports\FlexibleDate;
use App\Imports\InstallmentFromDescription;
use App\Imports\StatementTransaction;
use App\Imports\TransactionDirection;
use Carbon\CarbonImmutable;

final class RowParser
{
    public function __construct(
        private readonly FlexibleDate $dates,
        private readonly DecimalAmount $amounts,
        private readonly InstallmentFromDescription $installments,
    ) {}

    public function date(string $raw): ?CarbonImmutable
    {
        return $this->dates->parse($raw);
    }

    public function signedAmount(string $raw): ?string
    {
        return $this->amounts->normalize($raw);
    }

    public function build(
        CarbonImmutable $occurredAt,
        string $description,
        string $signedAmount,
        TransactionDirection $direction,
        ?string $externalId = null,
    ): StatementTransaction {
        $installment = $this->installments->extract($description);

        return new StatementTransaction(
            occurredAt: $occurredAt,
            description: $description,
            amount: ltrim($signedAmount, '-'),
            direction: $direction,
            externalId: $externalId,
            installmentNumber: $installment[0] ?? null,
            totalInstallments: $installment[1] ?? null,
        );
    }
}
