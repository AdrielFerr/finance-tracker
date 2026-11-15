<?php

namespace App\Imports\Ofx;

use Carbon\CarbonImmutable;

final class OfxDate
{
    public function parse(string $raw): ?CarbonImmutable
    {
        if (!preg_match('/(\d{4})(\d{2})(\d{2})/', $raw, $match)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', "{$match[1]}-{$match[2]}-{$match[3]}")
                ?->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
