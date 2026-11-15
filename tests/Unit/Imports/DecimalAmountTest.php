<?php

namespace Tests\Unit\Imports;

use App\Imports\DecimalAmount;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DecimalAmountTest extends TestCase
{
    private DecimalAmount $amount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->amount = new DecimalAmount();
    }

    #[DataProvider('valoresValidos')]
    public function test_normaliza_formatos_de_valor(string $raw, string $expected): void
    {
        $this->assertSame($expected, $this->amount->normalize($raw));
    }

    public static function valoresValidos(): array
    {
        return [
            'ponto decimal do padrao ofx' => ['-50.00', '-50.00'],
            'virgula decimal brasileira' => ['50,00', '50.00'],
            'milhar e decimal brasileiros' => ['1.234,56', '1234.56'],
            'milhar e decimal americanos' => ['1,234.56', '1234.56'],
            'varios grupos de milhar' => ['-1.234.567,89', '-1234567.89'],
            'milhar sem decimal' => ['1.234', '1234.00'],
            'inteiro puro' => ['300', '300.00'],
            'com simbolo de moeda' => ['R$ 99,90', '99.90'],
            'com espacos' => ['  -12,50  ', '-12.50'],
            'zero negativo vira zero' => ['-0.00', '0.00'],
            'uma casa decimal' => ['7,5', '7.50'],
        ];
    }

    #[DataProvider('valoresInvalidos')]
    public function test_recusa_valor_sem_digito(string $raw): void
    {
        $this->assertNull($this->amount->normalize($raw));
    }

    public static function valoresInvalidos(): array
    {
        return [
            'texto' => ['abc'],
            'vazio' => [''],
            'so separadores' => ['-,.'],
        ];
    }
}
