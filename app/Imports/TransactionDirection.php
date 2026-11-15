<?php

namespace App\Imports;

enum TransactionDirection: string
{
    case Outflow = 'outflow';
    case Inflow = 'inflow';

    public static function fromSignedAmount(string $amount): self
    {
        return str_starts_with(trim($amount), '-') ? self::Outflow : self::Inflow;
    }

    public function isOutflow(): bool
    {
        return $this === self::Outflow;
    }
}
