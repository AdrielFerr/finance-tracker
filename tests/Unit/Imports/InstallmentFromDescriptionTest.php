<?php

namespace Tests\Unit\Imports;

use App\Imports\InstallmentFromDescription;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InstallmentFromDescriptionTest extends TestCase
{
    private InstallmentFromDescription $installments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installments = new InstallmentFromDescription();
    }

    #[DataProvider('descricoesComParcela')]
    public function test_extrai_parcela(string $description, int $number, int $total): void
    {
        $this->assertSame([$number, $total], $this->installments->extract($description));
    }

    public static function descricoesComParcela(): array
    {
        return [
            'entre parenteses como no nubank' => ['Eletronicos LTDA (1/3)', 1, 3],
            'apos hifen no fim' => ['Loja Centro - 2/12', 2, 12],
            'com a palavra parcela' => ['Parcela 3/10 Mercado', 3, 10],
            'abreviada' => ['Curso parc. 4/6', 4, 6],
            'ultima parcela' => ['Movel Planejado (10/10)', 10, 10],
        ];
    }

    #[DataProvider('descricoesSemParcela')]
    public function test_ignora_o_que_nao_e_parcela(string $description): void
    {
        $this->assertNull($this->installments->extract($description));
    }

    public static function descricoesSemParcela(): array
    {
        return [
            'data dentro da descricao' => ['Compra 15/03 padaria'],
            'descricao simples' => ['Netflix'],
            'parcela unica nao e parcelamento' => ['Posto Shell (1/1)'],
            'numero zero' => ['Loja (0/5)'],
            'numero maior que o total' => ['Loja (7/3)'],
            'acima do limite aceito' => ['Loja (1/300)'],
        ];
    }
}
