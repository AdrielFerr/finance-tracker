<?php

namespace App\Imports\Csv\Profiles;

use App\Imports\Csv\ColumnNames;
use App\Imports\Csv\CsvProfile;
use App\Imports\Csv\RowParser;
use App\Imports\StatementTransaction;
use App\Imports\TransactionDirection;

final class NubankInvoiceProfile implements CsvProfile
{
    private const COLUMNS = ['date', 'title', 'amount'];

    public function __construct(
        private readonly ColumnNames $columns,
        private readonly RowParser $parser,
    ) {}

    public function name(): string
    {
        return 'Nubank - fatura';
    }

    public function matches(array $header): bool
    {
        return $this->columns->hasAll($header, self::COLUMNS);
    }

    public function toTransaction(array $row): ?StatementTransaction
    {
        $occurredAt = $this->parser->date($row['date'] ?? '');
        $signedAmount = $this->parser->signedAmount($row['amount'] ?? '');

        if ($occurredAt === null || $signedAmount === null) {
            return null;
        }

        return $this->parser->build(
            $occurredAt,
            $row['title'] ?? '',
            $signedAmount,
            $this->directionOf($signedAmount),
        );
    }

    private function directionOf(string $signedAmount): TransactionDirection
    {
        return str_starts_with($signedAmount, '-')
            ? TransactionDirection::Inflow
            : TransactionDirection::Outflow;
    }
}
