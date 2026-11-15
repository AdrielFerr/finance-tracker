<?php

namespace App\Imports\Csv\Profiles;

use App\Imports\Csv\ColumnNames;
use App\Imports\Csv\CsvProfile;
use App\Imports\Csv\RowParser;
use App\Imports\StatementTransaction;
use App\Imports\TransactionDirection;

final class NubankAccountProfile implements CsvProfile
{
    private const COLUMNS = ['data', 'valor', 'identificador', 'descricao'];

    public function __construct(
        private readonly ColumnNames $columns,
        private readonly RowParser $parser,
    ) {}

    public function name(): string
    {
        return 'Nubank - conta';
    }

    public function matches(array $header): bool
    {
        return $this->columns->hasAll($header, self::COLUMNS);
    }

    public function toTransaction(array $row): ?StatementTransaction
    {
        $occurredAt = $this->parser->date($row['data'] ?? '');
        $signedAmount = $this->parser->signedAmount($row['valor'] ?? '');

        if ($occurredAt === null || $signedAmount === null) {
            return null;
        }

        return $this->parser->build(
            $occurredAt,
            $row['descricao'] ?? '',
            $signedAmount,
            TransactionDirection::fromSignedAmount($signedAmount),
            $row['identificador'] ?: null,
        );
    }
}
