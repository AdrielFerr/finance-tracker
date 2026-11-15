<?php

namespace App\Imports\Csv\Profiles;

use App\Imports\Csv\CsvProfile;
use App\Imports\Csv\RowParser;
use App\Imports\StatementTransaction;
use App\Imports\TransactionDirection;

final class GenericProfile implements CsvProfile
{
    private const DATE_COLUMNS = ['data', 'date', 'data lancamento', 'data da compra', 'data movimento'];
    private const AMOUNT_COLUMNS = ['valor', 'amount', 'value', 'valor rs', 'quantia'];
    private const DESCRIPTION_COLUMNS = [
        'descricao', 'description', 'title', 'historico', 'movimento',
        'estabelecimento', 'origem destino', 'titulo', 'detalhe', 'lancamento',
    ];

    public function __construct(private readonly RowParser $parser) {}

    public function name(): string
    {
        return 'Layout genérico';
    }

    public function matches(array $header): bool
    {
        return $this->pick($header, self::DATE_COLUMNS) !== null
            && $this->pick($header, self::AMOUNT_COLUMNS) !== null
            && $this->pick($header, self::DESCRIPTION_COLUMNS) !== null;
    }

    public function toTransaction(array $row): ?StatementTransaction
    {
        $columns = array_keys($row);

        $dateColumn = $this->pick($columns, self::DATE_COLUMNS);
        $amountColumn = $this->pick($columns, self::AMOUNT_COLUMNS);
        $descriptionColumn = $this->pick($columns, self::DESCRIPTION_COLUMNS);

        if ($dateColumn === null || $amountColumn === null || $descriptionColumn === null) {
            return null;
        }

        $occurredAt = $this->parser->date($row[$dateColumn]);
        $signedAmount = $this->parser->signedAmount($row[$amountColumn]);

        if ($occurredAt === null || $signedAmount === null) {
            return null;
        }

        return $this->parser->build(
            $occurredAt,
            $row[$descriptionColumn],
            $signedAmount,
            TransactionDirection::fromSignedAmount($signedAmount),
        );
    }

    /**
     * @param array<int, string> $header
     * @param array<int, string> $candidates
     */
    private function pick(array $header, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $header, true)) {
                return $candidate;
            }
        }

        return null;
    }
}
