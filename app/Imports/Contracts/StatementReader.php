<?php

namespace App\Imports\Contracts;

use App\Imports\StatementTransaction;

interface StatementReader
{
    public function source(): string;

    public function supports(string $contents, string $extension): bool;

    /**
     * @return array<int, StatementTransaction>
     */
    public function read(string $contents): array;
}
