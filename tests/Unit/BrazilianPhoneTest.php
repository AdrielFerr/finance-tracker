<?php

namespace Tests\Unit;

use App\Services\WhatsApp\BrazilianPhone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BrazilianPhoneTest extends TestCase
{
    private BrazilianPhone $phones;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phones = new BrazilianPhone();
    }

    #[DataProvider('numerosValidos')]
    public function test_normaliza_numero(string $raw, string $expected): void
    {
        $this->assertSame($expected, $this->phones->toInternational($raw));
    }

    public static function numerosValidos(): array
    {
        return [
            'celular formatado' => ['(11) 99999-9999', '5511999999999'],
            'celular so digitos' => ['11999999999', '5511999999999'],
            'ja internacional' => ['5511999999999', '5511999999999'],
            'com mais e espacos' => ['+55 11 99999-9999', '5511999999999'],
            'fixo de oito digitos' => ['1133334444', '551133334444'],
            'ddd 55 nao e confundido com o pais' => ['(55) 99999-9999', '5555999999999'],
        ];
    }

    #[DataProvider('numerosInvalidos')]
    public function test_recusa_numero_invalido(string $raw): void
    {
        $this->assertNull($this->phones->toInternational($raw));
        $this->assertFalse($this->phones->isValid($raw));
    }

    public static function numerosInvalidos(): array
    {
        return [
            'curto demais' => ['123'],
            'vazio' => [''],
            'so texto' => ['meu numero'],
            'longo demais' => ['5511999999999999'],
        ];
    }
}
