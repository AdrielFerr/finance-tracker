<?php

namespace App\Imports;

final class InstallmentFromDescription
{
    private const MAX_INSTALLMENTS = 120;

    private const PATTERNS = [
        '/\((\d{1,3})\s*\/\s*(\d{1,3})\)/',
        '/\bparcela\s*(\d{1,3})\s*\/\s*(\d{1,3})\b/i',
        '/\bparc\.?\s*(\d{1,3})\s*\/\s*(\d{1,3})\b/i',
        '/\s-\s(\d{1,3})\/(\d{1,3})\s*$/',
    ];

    /**
     * @return array{0: int, 1: int}|null
     */
    public function extract(string $description): ?array
    {
        foreach (self::PATTERNS as $pattern) {
            if (!preg_match($pattern, $description, $match)) {
                continue;
            }

            $number = (int) $match[1];
            $total = (int) $match[2];

            if ($this->isValid($number, $total)) {
                return [$number, $total];
            }
        }

        return null;
    }

    private function isValid(int $number, int $total): bool
    {
        return $total >= 2
            && $total <= self::MAX_INSTALLMENTS
            && $number >= 1
            && $number <= $total;
    }
}
