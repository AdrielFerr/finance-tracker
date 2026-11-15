<?php

namespace App\Imports;

final class TextEncoding
{
    public function toUtf8(string $raw): string
    {
        $withoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;

        if (mb_check_encoding($withoutBom, 'UTF-8')) {
            return $withoutBom;
        }

        return mb_convert_encoding($withoutBom, 'UTF-8', 'Windows-1252');
    }
}
