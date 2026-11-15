<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_tela_de_despesas_traz_o_formulario_embutido(): void
    {
        $user = User::factory()->create();
        Category::factory()->for($user)->create(['name' => 'Moradia']);

        $response = $this->actingAs($user)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertSee('Nova despesa');
        $response->assertSee('Mais opções');
        $response->assertSee(route('expenses.store'));
        $response->assertSee('Moradia');
    }

    public function test_o_botao_nao_leva_mais_para_outra_pagina(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('expenses.index'));

        $response->assertDontSee('abrir-nova-despesa" class', false);
        $response->assertSee('abrir-nova-despesa', false);
    }

    public function test_cadastra_despesa_com_os_tres_campos_essenciais(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'description' => 'Conta de luz',
            'amount' => '243,90',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'category_id' => $category->id,
            'type' => 'variable',
        ]);

        $response->assertRedirect(route('expenses.index'));

        $expense = Expense::first();
        $this->assertSame('Conta de luz', $expense->description);
        $this->assertSame('243.90', $expense->amount);
        $this->assertSame($user->id, $expense->user_id);
    }

    public function test_erro_de_validacao_volta_para_a_lista_com_os_dados(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->from(route('expenses.index'))
            ->post(route('expenses.store'), [
                'description' => '',
                'amount' => '100,00',
                'due_date' => now()->format('Y-m-d'),
                'category_id' => $category->id,
                'type' => 'variable',
            ]);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHasErrors('description');
        $this->assertSame(0, Expense::count());
    }

    public function test_o_modal_reabre_quando_a_validacao_falha(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->from(route('expenses.index'))
            ->followingRedirects()
            ->post(route('expenses.store'), [
                'description' => '',
                'amount' => '100,00',
                'due_date' => now()->format('Y-m-d'),
                'category_id' => $category->id,
                'type' => 'variable',
            ]);

        $response->assertOk();
        $response->assertSee('open: true', false);
    }

    public function test_o_link_da_barra_lateral_abre_o_modal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('expenses.index', ['nova' => 1]));

        $response->assertOk();
        $response->assertSee('open: true', false);
    }

    public function test_a_pagina_antiga_continua_funcionando(): void
    {
        $user = User::factory()->create();
        Category::factory()->for($user)->create();

        $this->actingAs($user)->get(route('expenses.create'))->assertOk();
    }

    public function test_parcelamento_pelo_modal_gera_varias_despesas(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)->post(route('expenses.store'), [
            'description' => 'Geladeira',
            'amount' => '1200,00',
            'due_date' => now()->format('Y-m-d'),
            'category_id' => $category->id,
            'type' => 'variable',
            'is_installment' => '1',
            'total_installments' => 3,
        ]);

        $this->assertSame(3, Expense::count());
        $this->assertSame('400.00', Expense::first()->amount);
    }
}
