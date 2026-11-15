<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ImportBatch;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StatementImportTest extends TestCase
{
    use RefreshDatabase;

    private function ofxUpload(string $fixture = 'nubank-fatura.ofx'): UploadedFile
    {
        $source = __DIR__ . '/../Fixtures/Imports/' . $fixture;
        $copy = tempnam(sys_get_temp_dir(), 'ofx');
        copy($source, $copy);

        return new UploadedFile($copy, $fixture, 'application/octet-stream', null, true);
    }

    public function test_usuario_importa_arquivo_e_cai_na_tela_de_conferencia(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload(),
        ]);

        $batch = ImportBatch::first();

        $response->assertRedirect(route('expenses.import.show', $batch));
        $this->assertSame('reviewing', $batch->status);
        $this->assertSame($user->id, $batch->user_id);
        $this->assertSame('ofx', $batch->source);
        $this->assertCount(4, $batch->transactions);
    }

    public function test_nenhuma_despesa_e_criada_antes_da_confirmacao(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload(),
        ]);

        $this->assertSame(0, Expense::count());
    }

    public function test_tela_de_conferencia_lista_os_lancamentos(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);

        $response = $this->actingAs($user)->get(route('expenses.import.show', ImportBatch::first()));

        $response->assertOk();
        $response->assertSee('Padaria São José');
        $response->assertSee('parcela 1/3');
    }

    public function test_confirmacao_cria_despesas_somente_dos_selecionados(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $paymentMethod = PaymentMethod::factory()->for($user)->create();

        $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload(),
            'payment_method_id' => $paymentMethod->id,
        ]);

        $batch = ImportBatch::first();
        $outflows = $batch->transactions()->outflow()->orderBy('id')->get();
        $chosen = $outflows->first();

        $response = $this->actingAs($user)->post(route('expenses.import.confirm', $batch), [
            'selected' => [$chosen->id],
            'categories' => [$chosen->id => $category->id],
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertSame(1, Expense::count());

        $expense = Expense::first();
        $this->assertSame('Padaria São José', $expense->description);
        $this->assertSame('129.90', $expense->amount);
        $this->assertSame($category->id, $expense->category_id);
        $this->assertSame($paymentMethod->id, $expense->payment_method_id);
        $this->assertSame($user->id, $expense->user_id);
        $this->assertSame('2026-01-15', $expense->due_date->format('Y-m-d'));
        $this->assertSame('2026-01-01', $expense->competence_date->format('Y-m-d'));

        $this->assertSame('imported', $chosen->fresh()->status);
        $this->assertSame($expense->id, $chosen->fresh()->expense_id);
        $this->assertSame('confirmed', $batch->fresh()->status);
    }

    public function test_parcelamento_lido_do_arquivo_vai_para_a_despesa(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);

        $batch = ImportBatch::first();
        $installment = $batch->transactions()->whereNotNull('total_installments')->first();

        $this->actingAs($user)->post(route('expenses.import.confirm', $batch), [
            'selected' => [$installment->id],
            'categories' => [$installment->id => $category->id],
        ]);

        $expense = Expense::first();
        $this->assertTrue($expense->is_installment);
        $this->assertSame(1, $expense->installment_number);
        $this->assertSame(3, $expense->total_installments);
    }

    public function test_entrada_nao_pode_virar_despesa(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);

        $batch = ImportBatch::first();
        $inflow = $batch->transactions()->where('direction', 'inflow')->first();

        $this->actingAs($user)->post(route('expenses.import.confirm', $batch), [
            'selected' => [$inflow->id],
            'categories' => [$inflow->id => $category->id],
        ]);

        $this->assertSame(0, Expense::count());
        $this->assertSame('pending', $inflow->fresh()->status);
    }

    public function test_confirmacao_sem_categoria_e_recusada(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);

        $batch = ImportBatch::first();
        $transaction = $batch->transactions()->outflow()->first();

        $response = $this->actingAs($user)->post(route('expenses.import.confirm', $batch), [
            'selected' => [$transaction->id],
            'categories' => [],
        ]);

        $response->assertSessionHasErrors("categories.{$transaction->id}");
        $this->assertSame(0, Expense::count());
    }

    public function test_nao_aceita_categoria_de_outro_usuario(): void
    {
        $user = User::factory()->create();
        $categoriaDeOutro = Category::factory()->for(User::factory()->create())->create();

        $this->actingAs($user)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);

        $batch = ImportBatch::first();
        $transaction = $batch->transactions()->outflow()->first();

        $response = $this->actingAs($user)->post(route('expenses.import.confirm', $batch), [
            'selected' => [$transaction->id],
            'categories' => [$transaction->id => $categoriaDeOutro->id],
        ]);

        $response->assertSessionHasErrors("categories.{$transaction->id}");
        $this->assertSame(0, Expense::count());
    }

    public function test_nao_aceita_forma_de_pagamento_de_outro_usuario(): void
    {
        $user = User::factory()->create();
        $cartaoDeOutro = PaymentMethod::factory()->for(User::factory()->create())->create();

        $response = $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload(),
            'payment_method_id' => $cartaoDeOutro->id,
        ]);

        $response->assertSessionHasErrors('payment_method_id');
        $this->assertSame(0, ImportBatch::count());
    }

    public function test_usuario_nao_ve_importacao_de_outro(): void
    {
        $dono = User::factory()->create();
        $intruso = User::factory()->create();

        $this->actingAs($dono)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);
        $batch = ImportBatch::first();

        $this->actingAs($intruso)->get(route('expenses.import.show', $batch))->assertForbidden();
    }

    public function test_usuario_nao_confirma_importacao_de_outro(): void
    {
        $dono = User::factory()->create();
        $intruso = User::factory()->create();
        $categoriaDoIntruso = Category::factory()->for($intruso)->create();

        $this->actingAs($dono)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);
        $batch = ImportBatch::first();
        $transaction = $batch->transactions()->outflow()->first();

        $this->actingAs($intruso)->post(route('expenses.import.confirm', $batch), [
            'selected' => [$transaction->id],
            'categories' => [$transaction->id => $categoriaDoIntruso->id],
        ])->assertForbidden();

        $this->assertSame(0, Expense::count());
    }

    public function test_descartar_importacao_nao_cria_despesa(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);
        $batch = ImportBatch::first();

        $response = $this->actingAs($user)->delete(route('expenses.import.destroy', $batch));

        $response->assertRedirect(route('expenses.index'));
        $this->assertSame('discarded', $batch->fresh()->status);
        $this->assertSame(0, Expense::count());
        $this->assertSame(0, $batch->transactions()->where('status', 'pending')->count());
    }

    public function test_reimportar_o_mesmo_arquivo_marca_possivel_repetido(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);
        $primeiro = ImportBatch::first();
        $transaction = $primeiro->transactions()->outflow()->first();

        $this->actingAs($user)->post(route('expenses.import.confirm', $primeiro), [
            'selected' => [$transaction->id],
            'categories' => [$transaction->id => $category->id],
        ]);

        $this->actingAs($user)->post(route('expenses.import.store'), ['statement' => $this->ofxUpload()]);
        $segundo = ImportBatch::latest('id')->first();

        $repetido = $segundo->transactions()->where('signature', $transaction->signature)->first();
        $novo = $segundo->transactions()->where('signature', '!=', $transaction->signature)->first();

        $this->assertTrue($repetido->is_possible_duplicate);
        $this->assertFalse($novo->is_possible_duplicate);
    }

    public function test_arquivo_de_formato_nao_suportado_e_recusado(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => UploadedFile::fake()->createWithContent('fatura.pdf', '%PDF-1.4'),
        ]);

        $response->assertSessionHasErrors('statement');
        $this->assertSame(0, ImportBatch::count());
    }

    public function test_visitante_nao_importa_nada(): void
    {
        $this->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload(),
        ])->assertRedirect(route('login'));

        $this->assertSame(0, ImportBatch::count());
    }

    public function test_extrato_do_bradesco_tambem_e_lido(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload('bradesco-extrato.ofx'),
        ]);

        $batch = ImportBatch::first();

        $this->assertCount(3, $batch->transactions);
        $this->assertSame(2, $batch->transactions()->outflow()->count());
        $this->assertDatabaseHas('imported_transactions', [
            'description' => 'DEB AUTOM CONTA DE LUZ',
            'amount' => '89.90',
            'direction' => 'outflow',
        ]);
    }

    public function test_importa_fatura_do_nubank_em_csv(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload('nubank-fatura.csv'),
        ]);

        $batch = ImportBatch::first();

        $this->assertSame('csv', $batch->source);
        $this->assertCount(4, $batch->transactions);
        $this->assertSame(3, $batch->transactions()->outflow()->count());

        $compra = $batch->transactions()->outflow()->orderBy('id')->first();

        $this->actingAs($user)->post(route('expenses.import.confirm', $batch), [
            'selected' => [$compra->id],
            'categories' => [$compra->id => $category->id],
        ]);

        $expense = Expense::first();
        $this->assertSame('Padaria Sao Jose', $expense->description);
        $this->assertSame('129.90', $expense->amount);
    }

    public function test_importa_extrato_do_inter_em_csv_com_acentuacao(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload('inter-extrato.csv'),
        ]);

        $batch = ImportBatch::first();

        $this->assertCount(3, $batch->transactions);
        $this->assertDatabaseHas('imported_transactions', [
            'description' => 'Pagamento efetuado - CONTA DE LUZ CEMIG',
            'amount' => '89.90',
            'direction' => 'outflow',
        ]);
    }

    public function test_importa_extrato_do_bradesco_em_csv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload('bradesco-extrato.csv'),
        ]);

        $batch = ImportBatch::first();

        $this->assertSame(2, $batch->transactions()->outflow()->count());
        $this->assertSame(1, $batch->transactions()->where('direction', 'inflow')->count());
    }

    public function test_importa_extrato_do_picpay_pelo_perfil_generico(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => $this->ofxUpload('picpay-extrato.csv'),
        ]);

        $batch = ImportBatch::first();

        $this->assertCount(2, $batch->transactions);
        $this->assertDatabaseHas('imported_transactions', [
            'description' => 'Loja XYZ Comercio',
            'amount' => '32.50',
            'direction' => 'outflow',
        ]);
    }

    public function test_importacao_vive_dentro_da_tela_de_despesas(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertSee('Importar extrato');
        $response->assertSee(route('expenses.import.store'));
    }

    public function test_erro_de_importacao_volta_para_a_tela_de_despesas(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('expenses.index'))
            ->post(route('expenses.import.store'), [
                'statement' => UploadedFile::fake()->createWithContent('estranho.csv', "coluna a;coluna b\n1;2"),
            ]);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('error');
    }

    public function test_csv_com_colunas_desconhecidas_avisa_em_vez_de_inventar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('expenses.import.store'), [
            'statement' => UploadedFile::fake()->createWithContent('estranho.csv', "coluna a;coluna b\n1;2"),
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, ImportBatch::count());
    }
}
