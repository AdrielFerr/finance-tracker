<?php

namespace Tests\Unit\Imports;

use App\Imports\DecimalAmount;
use App\Imports\StatementTransaction;
use App\Imports\InstallmentFromDescription;
use App\Imports\TextEncoding;
use App\Imports\Ofx\OfxDate;
use App\Imports\Ofx\OfxTags;
use App\Imports\Readers\OfxStatementReader;
use App\Imports\TransactionDirection;
use PHPUnit\Framework\TestCase;

class OfxStatementReaderTest extends TestCase
{
    private OfxStatementReader $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reader = new OfxStatementReader(
            new TextEncoding(),
            new OfxTags(),
            new OfxDate(),
            new DecimalAmount(),
            new InstallmentFromDescription(),
        );
    }

    private function fixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/../../Fixtures/Imports/' . $name);
    }

    public function test_le_todas_as_transacoes_da_fatura_do_nubank(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.ofx'));

        $this->assertCount(4, $transactions);
        $this->assertContainsOnlyInstancesOf(StatementTransaction::class, $transactions);
    }

    public function test_converte_acentuacao_do_windows_1252_para_utf8(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.ofx'));

        $this->assertSame('Padaria São José', $transactions[0]->description);
        $this->assertTrue(mb_check_encoding($transactions[1]->description, 'UTF-8'));
        $this->assertStringContainsString('Eletrônicos Informática', $transactions[1]->description);
    }

    public function test_compra_vira_saida_e_pagamento_vira_entrada(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.ofx'));

        $this->assertSame(TransactionDirection::Outflow, $transactions[0]->direction);
        $this->assertSame(TransactionDirection::Inflow, $transactions[2]->direction);
    }

    public function test_valor_fica_sempre_positivo_com_duas_casas(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.ofx'));

        $this->assertSame('129.90', $transactions[0]->amount);
        $this->assertSame('1250.00', $transactions[1]->amount);
        $this->assertSame('500.00', $transactions[2]->amount);
    }

    public function test_extrai_data_ignorando_hora_e_fuso(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.ofx'));

        $this->assertSame('2026-01-15', $transactions[0]->occurredAt->format('Y-m-d'));
    }

    public function test_reconhece_parcelamento_na_descricao(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.ofx'));

        $this->assertTrue($transactions[1]->isInstallment());
        $this->assertSame(1, $transactions[1]->installmentNumber);
        $this->assertSame(3, $transactions[1]->totalInstallments);
        $this->assertFalse($transactions[0]->isInstallment());
    }

    public function test_usa_name_quando_nao_existe_memo(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.ofx'));

        $this->assertSame('UBER *TRIP', $transactions[3]->description);
    }

    public function test_guarda_o_fitid_como_identificador_externo(): void
    {
        $transactions = $this->reader->read($this->fixture('nubank-fatura.ofx'));

        $this->assertSame('6512a9f1e4b0c7d2', $transactions[0]->externalId);
    }

    public function test_le_extrato_bradesco_no_formato_xml(): void
    {
        $transactions = $this->reader->read($this->fixture('bradesco-extrato.ofx'));

        $this->assertCount(3, $transactions);
        $this->assertSame('DEB AUTOM CONTA DE LUZ', $transactions[0]->description);
        $this->assertSame('89.90', $transactions[0]->amount);
        $this->assertSame(TransactionDirection::Outflow, $transactions[0]->direction);
        $this->assertSame(TransactionDirection::Inflow, $transactions[1]->direction);
        $this->assertSame('1234.56', $transactions[2]->amount);
    }

    public function test_nao_confunde_saldo_final_com_transacao(): void
    {
        $transactions = $this->reader->read($this->fixture('bradesco-extrato.ofx'));

        $amounts = array_map(fn (StatementTransaction $t) => $t->amount, $transactions);

        $this->assertNotContains('2175.64', $amounts);
    }

    public function test_reconhece_arquivos_ofx_dos_dois_dialetos(): void
    {
        $this->assertTrue($this->reader->supports($this->fixture('nubank-fatura.ofx'), 'ofx'));
        $this->assertTrue($this->reader->supports($this->fixture('bradesco-extrato.ofx'), 'OFX'));
        $this->assertTrue($this->reader->supports($this->fixture('nubank-fatura.ofx'), 'txt'));
        $this->assertFalse($this->reader->supports('data;descricao;valor', 'csv'));
    }

    public function test_ignora_transacao_sem_data_ou_sem_valor(): void
    {
        $contents = '<OFX><STMTTRN><TRNAMT>-10.00<MEMO>Sem data</STMTTRN>'
            . '<STMTTRN><DTPOSTED>20260101<MEMO>Sem valor</STMTTRN>'
            . '<STMTTRN><DTPOSTED>20260102<TRNAMT>-5.00<MEMO>Valida</STMTTRN></OFX>';

        $transactions = $this->reader->read($contents);

        $this->assertCount(1, $transactions);
        $this->assertSame('Valida', $transactions[0]->description);
    }

    public function test_arquivo_sem_transacoes_devolve_lista_vazia(): void
    {
        $this->assertSame([], $this->reader->read('<OFX><SIGNONMSGSRSV1></SIGNONMSGSRSV1></OFX>'));
    }

    public function test_assinatura_usa_fitid_e_se_mantem_estavel(): void
    {
        $first = $this->reader->read($this->fixture('nubank-fatura.ofx'));
        $second = $this->reader->read($this->fixture('nubank-fatura.ofx'));

        $this->assertSame($first[0]->signature(), $second[0]->signature());
        $this->assertNotSame($first[0]->signature(), $first[1]->signature());
    }
}
