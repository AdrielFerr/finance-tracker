<?php

namespace App\Imports;

use App\Imports\Contracts\StatementReader;
use App\Imports\Exceptions\UnsupportedStatementException;

final class StatementReaderResolver
{
    /**
     * @param array<int, StatementReader> $readers
     */
    public function __construct(private readonly array $readers) {}

    public function resolve(string $contents, string $extension, string $filename): StatementReader
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($contents, $extension)) {
                return $reader;
            }
        }

        throw UnsupportedStatementException::forFile($filename);
    }
}
