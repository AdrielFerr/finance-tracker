<?php

namespace App\Services\WhatsApp;

final class BrazilianPhone
{
    private const COUNTRY_CODE = '55';
    private const NATIONAL_LENGTHS = [10, 11];
    private const INTERNATIONAL_LENGTHS = [12, 13];

    public function toInternational(string $raw): ?string
    {
        $digits = preg_replace('/\D/', '', $raw) ?? '';

        if (in_array(strlen($digits), self::NATIONAL_LENGTHS, true)) {
            return self::COUNTRY_CODE . $digits;
        }

        if (in_array(strlen($digits), self::INTERNATIONAL_LENGTHS, true)) {
            return $digits;
        }

        return null;
    }

    public function isValid(string $raw): bool
    {
        return $this->toInternational($raw) !== null;
    }
}
