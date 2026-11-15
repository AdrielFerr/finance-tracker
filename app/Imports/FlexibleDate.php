<?php

namespace App\Imports;

use Carbon\CarbonImmutable;

final class FlexibleDate
{
    private const FORMATS = ['Y-m-d', 'd/m/Y', 'd/m/y', 'd-m-Y', 'Y/m/d'];

    public function parse(string $raw): ?CarbonImmutable
    {
        $value = trim($raw);

        if ($value === '') {
            return null;
        }

        foreach (self::FORMATS as $format) {
            $date = $this->tryFormat($value, $format);

            if ($date !== null) {
                return $date;
            }
        }

        return null;
    }

    private function tryFormat(string $value, string $format): ?CarbonImmutable
    {
        try {
            $date = CarbonImmutable::createFromFormat('!' . $format, $value);
        } catch (\Throwable) {
            return null;
        }

        if ($date === false || $date->format($format) !== $value) {
            return null;
        }

        return $date;
    }
}
