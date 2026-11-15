<?php

namespace App\Imports\Csv\Profiles;

use App\Imports\Csv\ColumnNames;
use App\Imports\Csv\CsvProfile;
use App\Imports\Csv\RowParser;
use App\Imports\StatementTransaction;
use App\Imports\TransactionDirection;

final class InterProfile implements CsvProfile
{
    private const COLUMNS = ['data lancamento', 'historico', 'valor'];

    public function __construct(
        private readonly ColumnNames $columns,
        private readonly RowParser $parser,
    ) {}

    public function name(): string
    {
        return 'Banco Inter - extrato';
    }

    public function matches(array $header): bool
    {
        return $this->columns->hasAll($header, self::COLUMNS);
    }

    public function toTransaction(array $row): ?StatementTransaction
    {
        $occurredAt = $this->parser->date($row['data lancamento'] ?? '');
        $signedAmount = $this->parser->signedAmount($row['valor'] ?? '');

        if ($occurredAt === null || $signedAmount === null) {
            return null;
        }

        return $this->parser->build(
            $occurredAt,
            $this->describe($row),
            $signedAmount,
            TransactionDirection::fromSignedAmount($signedAmount),
        );
    }

    /**
     * @param array<string, string> $row
     */
    private function describe(array $row): string
    {
        $parts = array_filter([
            $row['historico'] ?? '',
            $row['descricao'] ?? '',
        ], fn (string $part) => trim($part) !== '');

        return implode(' - ', array_unique($parts));
    }
}
