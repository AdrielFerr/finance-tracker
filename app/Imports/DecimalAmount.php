<?php

namespace App\Imports;

final class DecimalAmount
{
    private const THOUSAND_GROUP_SIZE = 3;

    public function normalize(string $raw): ?string
    {
        $cleaned = preg_replace('/[^0-9,.\-]/', '', $raw) ?? '';
        $isNegative = str_starts_with($cleaned, '-');
        $digits = str_replace('-', '', $cleaned);

        if (!preg_match('/\d/', $digits)) {
            return null;
        }

        $value = (float) $this->toPlainNumber($digits);

        return ($isNegative && $value > 0 ? '-' : '') . number_format($value, 2, '.', '');
    }

    private function toPlainNumber(string $digits): string
    {
        $hasComma = str_contains($digits, ',');
        $hasDot = str_contains($digits, '.');

        if ($hasComma && $hasDot) {
            return strrpos($digits, ',') > strrpos($digits, '.')
                ? str_replace(',', '.', str_replace('.', '', $digits))
                : str_replace(',', '', $digits);
        }

        if ($hasComma) {
            return $this->resolveSingleSeparator($digits, ',');
        }

        if ($hasDot) {
            return $this->resolveSingleSeparator($digits, '.');
        }

        return $digits;
    }

    private function resolveSingleSeparator(string $digits, string $separator): string
    {
        $parts = explode($separator, $digits);
        $decimals = strlen(end($parts));

        if (count($parts) > 2 || $decimals === self::THOUSAND_GROUP_SIZE) {
            return str_replace($separator, '', $digits);
        }

        return str_replace($separator, '.', $digits);
    }
}
