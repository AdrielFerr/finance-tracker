<?php

namespace App\Imports\Readers;

use App\Imports\Contracts\StatementReader;
use App\Imports\Csv\ColumnNames;
use App\Imports\Csv\CsvDocument;
use App\Imports\Csv\CsvProfile;
use App\Imports\Exceptions\UnsupportedStatementException;
use App\Imports\StatementTransaction;

final class CsvStatementReader implements StatementReader
{
    private const HEADER_SEARCH_LIMIT = 25;
    private const EXTENSIONS = ['csv', 'txt'];

    /**
     * @param array<int, CsvProfile> $profiles
     */
    public function __construct(
        private readonly CsvDocument $document,
        private readonly ColumnNames $columns,
        private readonly array $profiles,
    ) {}

    public function source(): string
    {
        return 'csv';
    }

    public function supports(string $contents, string $extension): bool
    {
        return in_array(strtolower($extension), self::EXTENSIONS, true);
    }

    /**
     * @return array<int, StatementTransaction>
     */
    public function read(string $contents): array
    {
        $rows = $this->document->rows($contents);
        $found = $this->findHeader($rows);

        if ($found === null) {
            throw UnsupportedStatementException::forUnknownLayout();
        }

        [$profile, $headerIndex, $header] = $found;

        return $this->readRows($profile, $header, array_slice($rows, $headerIndex + 1));
    }

    /**
     * @param array<int, array<int, string>> $rows
     * @return array{0: CsvProfile, 1: int, 2: array<int, string>}|null
     */
    private function findHeader(array $rows): ?array
    {
        foreach (array_slice($rows, 0, self::HEADER_SEARCH_LIMIT, true) as $index => $row) {
            $header = $this->columns->normalizeAll($row);

            foreach ($this->profiles as $profile) {
                if ($profile->matches($header)) {
                    return [$profile, $index, $header];
                }
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $header
     * @param array<int, array<int, string>> $rows
     * @return array<int, StatementTransaction>
     */
    private function readRows(CsvProfile $profile, array $header, array $rows): array
    {
        $transactions = [];

        foreach ($rows as $row) {
            $associated = $this->associate($header, $row);

            if ($associated === null) {
                continue;
            }

            $transaction = $profile->toTransaction($associated);

            if ($transaction !== null) {
                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }

    /**
     * @param array<int, string> $header
     * @param array<int, string> $row
     * @return array<string, string>|null
     */
    private function associate(array $header, array $row): ?array
    {
        if (count($row) < count($header)) {
            $row = array_pad($row, count($header), '');
        }

        return array_combine($header, array_slice($row, 0, count($header))) ?: null;
    }
}
