<?php

namespace App\Imports\Csv;

use App\Imports\TextEncoding;

final class CsvDocument
{
    private const SAMPLE_LINES = 20;

    public function __construct(private readonly TextEncoding $encoding) {}

    /**
     * @return array<int, array<int, string>>
     */
    public function rows(string $contents): array
    {
        $lines = $this->lines($this->encoding->toUtf8($contents));
        $delimiter = $this->detectDelimiter($lines);

        $rows = [];

        foreach ($lines as $line) {
            $rows[] = array_map(trim(...), str_getcsv($line, $delimiter, '"', ''));
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function lines(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        return array_values(array_filter($lines, fn (string $line) => trim($line) !== ''));
    }

    /**
     * @param array<int, string> $lines
     */
    private function detectDelimiter(array $lines): string
    {
        $sample = implode("\n", array_slice($lines, 0, self::SAMPLE_LINES));

        return substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',';
    }
}
