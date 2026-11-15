<?php

namespace App\Imports\Exceptions;

use RuntimeException;

class UnsupportedStatementException extends RuntimeException
{
    public static function forFile(string $filename): self
    {
        return new self("Não foi possível reconhecer o formato do arquivo \"{$filename}\". O sistema lê arquivos OFX e CSV.");
    }

    public static function forUnknownLayout(): self
    {
        return new self('As colunas deste CSV não foram reconhecidas. Confira se o arquivo tem colunas de data, descrição e valor.');
    }
}
