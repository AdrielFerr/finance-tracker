<?php

namespace App\Imports\Csv;

final class ColumnNames
{
    public function normalize(string $column): string
    {
        $withoutAccents = mb_convert_encoding(
            iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $column) ?: $column,
            'UTF-8'
        );

        $clean = preg_replace('/[^a-z0-9 ]/', '', mb_strtolower(trim($withoutAccents))) ?? '';

        return trim(preg_replace('/\s+/', ' ', $clean) ?? '');
    }

    /**
     * @param array<int, string> $columns
     * @return array<int, string>
     */
    public function normalizeAll(array $columns): array
    {
        return array_map(fn (string $column) => $this->normalize($column), $columns);
    }

    /**
     * @param array<int, string> $header
     * @param array<int, string> $required
     */
    public function hasAll(array $header, array $required): bool
    {
        return count(array_intersect($required, $header)) === count($required);
    }
}
