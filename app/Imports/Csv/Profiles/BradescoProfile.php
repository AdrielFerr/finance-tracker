<?php

namespace App\Imports\Csv\Profiles;

use App\Imports\Csv\ColumnNames;
use App\Imports\Csv\CsvProfile;
use App\Imports\Csv\RowParser;
use App\Imports\StatementTransaction;
use App\Imports\TransactionDirection;

final class BradescoProfile implements CsvProfile
{
    private const COLUMNS = ['data', 'historico', 'credito', 'debito'];

    public function __construct(
        private readonly ColumnNames $columns,
        private readonly RowParser $parser,
    ) {}

    public function name(): string
    {
        return 'Bradesco - extrato';
    }

    public function matches(array $header): bool
    {
        return $this->columns->hasAll($header, self::COLUMNS);
    }

    public function toTransaction(array $row): ?StatementTransaction
    {
        $occurredAt = $this->parser->date($row['data'] ?? '');
        $debit = $this->parser->signedAmount($row['debito'] ?? '');
        $credit = $this->parser->signedAmount($row['credito'] ?? '');

        if ($occurredAt === null) {
            return null;
        }

        return match (true) {
            $this->isFilled($debit) => $this->parser->build($occurredAt, $this->describe($row), $debit, TransactionDirection::Outflow),
            $this->isFilled($credit) => $this->parser->build($occurredAt, $this->describe($row), $credit, TransactionDirection::Inflow),
            default => null,
        };
    }

    private function isFilled(?string $amount): bool
    {
        return $amount !== null && (float) $amount !== 0.0;
    }

    /**
     * @param array<string, string> $row
     */
    private function describe(array $row): string
    {
        return trim($row['historico'] ?? '');
    }
}
