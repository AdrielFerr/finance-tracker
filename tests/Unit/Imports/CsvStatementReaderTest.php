<?php

namespace Tests\Unit\Imports;

use App\Imports\Csv\ColumnNames;
use App\Imports\Csv\CsvDocument;
use App\Imports\Csv\Profiles\BradescoProfile;
use App\Imports\Csv\Profiles\GenericProfile;
use App\Imports\Csv\Profiles\InterProfile;
use App\Imports\Csv\Profiles\NubankAccountProfile;
use App\Imports\Csv\Profiles\NubankInvoiceProfile;
use App\Imports\Csv\RowParser;
use App\Imports\DecimalAmount;
use App\Imports\Exceptions\UnsupportedStatementException;
use App\Imports\FlexibleDate;
use App\Imports\InstallmentFromDescription;
use App\Imports\Readers\CsvStatementReader;
use App\Imports\TextEncoding;
use App\Imports\TransactionDirection;
use PHPUnit\Framework\TestCase;

class CsvStatementReaderTest extends TestCase
{
    private CsvStatementReader $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $columns = new ColumnNames();
        $parser = new RowParser(new FlexibleDate(), new DecimalAmount(), new InstallmentFromDescription());

        $this->reader = new CsvStatementReader(
            new CsvDocument(new TextEncoding()),
            $columns,
            [
                new NubankInvoiceProfile($columns, $parser),
                new NubankAccountProfile($columns, $parser),
                new InterProfile($columns, $parser),
                new BradescoProfile($columns, $parser),
                new GenericProfile($parser),
            ]
        );
    }

    private function fixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/../../Fixtures/Imports/' . $name);
    }

    public function test_le_fatura_do_nubank_tratando_compra_como_saida(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.csv'));

        $this->assertCount(4, $transactions);
        $this->assertSame('Padaria Sao Jose', $transactions[0]->description);
        $this->assertSame('129.90', $transactions[0]->amount);
        $this->assertSame(TransactionDirection::Outflow, $transactions[0]->direction);
        $this->assertSame('2026-01-15', $transactions[0]->occurredAt->format('Y-m-d'));
    }

    public function test_pagamento_de_fatura_do_nubank_e_entrada(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.csv'));

        $this->assertSame('Pagamento recebido', $transactions[2]->description);
        $this->assertSame(TransactionDirection::Inflow, $transactions[2]->direction);
        $this->assertSame('500.00', $transactions[2]->amount);
    }

    public function test_parcela_na_fatura_do_nubank_e_reconhecida(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.csv'));

        $this->assertTrue($transactions[1]->isInstallment());
        $this->assertSame(1, $transactions[1]->installmentNumber);
        $this->assertSame(3, $transactions[1]->totalInstallments);
    }

    public function test_le_conta_do_nubank_usando_identificador_como_id_externo(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-conta.csv'));

        $this->assertCount(3, $transactions);
        $this->assertSame('67e8b2a1-0001', $transactions[0]->externalId);
        $this->assertSame(TransactionDirection::Outflow, $transactions[0]->direction);
        $this->assertSame('45.90', $transactions[0]->amount);
        $this->assertSame(TransactionDirection::Inflow, $transactions[1]->direction);
    }

    public function test_le_inter_pulando_as_linhas_de_cabecalho_do_banco(): void
    {
        $transactions = $this->reader->read($this->fixture('inter-extrato.csv'));

        $this->assertCount(3, $transactions);
        $this->assertSame('2026-01-05', $transactions[0]->occurredAt->format('Y-m-d'));
        $this->assertSame('89.90', $transactions[0]->amount);
        $this->assertSame(TransactionDirection::Outflow, $transactions[0]->direction);
    }

    public function test_inter_junta_historico_com_descricao(): void
    {
        $transactions = $this->reader->read($this->fixture('inter-extrato.csv'));

        $this->assertSame('Pagamento efetuado - CONTA DE LUZ CEMIG', $transactions[0]->description);
    }

    public function test_inter_converte_acentuacao_e_valor_brasileiro(): void
    {
        $transactions = $this->reader->read($this->fixture('inter-extrato.csv'));

        $this->assertStringContainsString('Compra no débito', $transactions[2]->description);
        $this->assertTrue(mb_check_encoding($transactions[2]->description, 'UTF-8'));
        $this->assertSame('234.10', $transactions[2]->amount);
        $this->assertSame('3500.00', $transactions[1]->amount);
    }

    public function test_bradesco_separa_credito_de_debito_em_colunas(): void
    {
        $transactions = $this->reader->read($this->fixture('bradesco-extrato.csv'));

        $this->assertCount(3, $transactions);

        $this->assertSame(TransactionDirection::Outflow, $transactions[0]->direction);
        $this->assertSame('89.90', $transactions[0]->amount);
        $this->assertSame('DEB AUTOM CONTA DE LUZ', $transactions[0]->description);

        $this->assertSame(TransactionDirection::Inflow, $transactions[1]->direction);
        $this->assertSame('3500.00', $transactions[1]->amount);

        $this->assertSame(TransactionDirection::Outflow, $transactions[2]->direction);
        $this->assertSame('1234.56', $transactions[2]->amount);
    }

    public function test_bradesco_ignora_a_linha_solta_antes_do_cabecalho(): void
    {
        $transactions = $this->reader->read($this->fixture('bradesco-extrato.csv'));

        $descriptions = array_map(fn ($t) => $t->description, $transactions);

        $this->assertNotContains('Extrato de: 01/01/2026 a 31/01/2026', $descriptions);
    }

    public function test_layout_desconhecido_cai_no_perfil_generico(): void
    {
        $transactions = $this->reader->read($this->fixture('picpay-extrato.csv'));

        $this->assertCount(2, $transactions);
        $this->assertSame('Loja XYZ Comercio', $transactions[0]->description);
        $this->assertSame('32.50', $transactions[0]->amount);
        $this->assertSame(TransactionDirection::Outflow, $transactions[0]->direction);
        $this->assertSame(TransactionDirection::Inflow, $transactions[1]->direction);
    }

    public function test_csv_sem_colunas_reconheciveis_avisa_em_vez_de_inventar(): void
    {
        $this->expectException(UnsupportedStatementException::class);

        $this->reader->read("coluna a;coluna b;coluna c\n1;2;3");
    }

    public function test_ignora_linha_com_data_ou_valor_invalido(): void
    {
        $contents = "date,title,amount\n2026-01-15,Valida,10.00\nsem data,Invalida,20.00\n2026-01-17,Sem valor,";

        $transactions = $this->reader->read($contents);

        $this->assertCount(1, $transactions);
        $this->assertSame('Valida', $transactions[0]->description);
    }

    public function test_reconhece_pela_extensao(): void
    {
        $this->assertTrue($this->reader->supports('', 'csv'));
        $this->assertTrue($this->reader->supports('', 'CSV'));
        $this->assertTrue($this->reader->supports('', 'txt'));
        $this->assertFalse($this->reader->supports('', 'ofx'));
        $this->assertFalse($this->reader->supports('', 'pdf'));
    }

    public function test_cada_banco_cai_no_proprio_perfil(): void
    {
        $nubankInvoice = $this->reader->read($this->fixture('nubank-fatura.csv'));
        $bradesco = $this->reader->read($this->fixture('bradesco-extrato.csv'));

        $this->assertSame('Padaria Sao Jose', $nubankInvoice[0]->description);
        $this->assertSame('DEB AUTOM CONTA DE LUZ', $bradesco[0]->description);
    }
}
