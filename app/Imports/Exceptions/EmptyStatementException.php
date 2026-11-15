<?php

namespace App\Imports\Exceptions;

use RuntimeException;

class EmptyStatementException extends RuntimeException
{
    public static function forFile(string $filename): self
    {
        return new self("Nenhum lançamento foi encontrado em \"{$filename}\".");
    }
}
