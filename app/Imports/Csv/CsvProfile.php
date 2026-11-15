<?php

namespace App\Imports\Csv;

use App\Imports\StatementTransaction;

interface CsvProfile
{
    public function name(): string;

    /**
     * @param array<int, string> $header
     */
    public function matches(array $header): bool;

    /**
     * @param array<string, string> $row
     */
    public function toTransaction(array $row): ?StatementTransaction;
}
