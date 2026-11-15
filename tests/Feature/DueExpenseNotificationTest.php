<?php

namespace Tests\Feature;

use App\Mail\DueExpensesMail;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DueExpenseNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function userWithEmail(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'is_active' => true,
            'notify_in_app' => false,
            'notify_by_email' => true,
            'notify_by_whatsapp' => false,
            'notify_days_before' => 3,
            'notify_on_due_date' => true,
            'notify_when_overdue' => false,
        ], $attributes));
    }

    private function expenseFor(User $user, int $daysFromNow, array $attributes = []): Expense
    {
        return Expense::factory()
            ->for($user)
            ->for(Category::factory()->for($user))
            ->dueIn($daysFromNow)
            ->create($attributes);
    }

    public function test_avisa_por_email_despesa_que_vence_dentro_do_prazo(): void
    {
        Mail::fake();

        $user = $this->userWithEmail();
        $this->expenseFor($user, 2);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertSent(DueExpensesMail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_nao_avisa_despesa_fora_do_prazo_configurado(): void
    {
        Mail::fake();

        $user = $this->userWithEmail(['notify_days_before' => 3]);
        $this->expenseFor($user, 10);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_prazo_maior_passa_a_pegar_a_mesma_despesa(): void
    {
        Mail::fake();

        $user = $this->userWithEmail(['notify_days_before' => 15]);
        $this->expenseFor($user, 10);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertSent(DueExpensesMail::class);
    }

    public function test_nao_avisa_duas_vezes_a_mesma_despesa(): void
    {
        Mail::fake();

        $user = $this->userWithEmail();
        $this->expenseFor($user, 2);

        $this->artisan('expenses:notify-due')->assertSuccessful();
        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertSentCount(1);
        $this->assertSame(1, ExpenseNotification::count());
    }

    public function test_despesa_ja_paga_nao_gera_aviso(): void
    {
        Mail::fake();

        $user = $this->userWithEmail();
        $this->expenseFor($user, 2, ['status' => 'paid', 'payment_date' => now()]);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_avisa_no_dia_do_vencimento_quando_ligado(): void
    {
        Mail::fake();

        $user = $this->userWithEmail(['notify_on_due_date' => true]);
        $this->expenseFor($user, 0);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertSent(DueExpensesMail::class, fn ($mail) => $mail->kind->value === 'due_today');
    }

    public function test_nao_avisa_no_dia_quando_desligado(): void
    {
        Mail::fake();

        $user = $this->userWithEmail(['notify_on_due_date' => false]);
        $this->expenseFor($user, 0);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_avisa_despesa_vencida_so_quando_ligado(): void
    {
        Mail::fake();

        $user = $this->userWithEmail(['notify_when_overdue' => true]);
        $this->expenseFor($user, -5);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertSent(DueExpensesMail::class, fn ($mail) => $mail->kind->value === 'overdue');
    }

    public function test_usuario_so_recebe_as_proprias_despesas(): void
    {
        Mail::fake();

        $dono = $this->userWithEmail();
        $outro = $this->userWithEmail();

        $this->expenseFor($dono, 2);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertSent(DueExpensesMail::class, fn ($mail) => $mail->hasTo($dono->email));
        Mail::assertNotSent(DueExpensesMail::class, fn ($mail) => $mail->hasTo($outro->email));
    }

    public function test_quem_desligou_o_email_nao_recebe(): void
    {
        Mail::fake();

        $user = $this->userWithEmail(['notify_by_email' => false]);
        $this->expenseFor($user, 2);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_usuario_inativo_nao_recebe(): void
    {
        Mail::fake();

        $user = $this->userWithEmail(['is_active' => false]);
        $this->expenseFor($user, 2);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_envia_pelo_whatsapp_usando_o_chatid_resolvido_pelo_waha(): void
    {
        Mail::fake();
        config(['waha.url' => 'http://waha.local:3000', 'waha.api_key' => 'segredo', 'waha.session' => 'default']);

        Http::fake([
            '*/api/contacts/check-exists*' => Http::response(['numberExists' => true, 'chatId' => '551199999999@c.us']),
            '*/api/sendText' => Http::response(['id' => 'abc']),
        ]);

        $user = $this->userWithEmail([
            'notify_by_email' => false,
            'notify_by_whatsapp' => true,
            'whatsapp_number' => '(11) 99999-9999',
        ]);
        $this->expenseFor($user, 2, ['description' => 'Aluguel', 'amount' => 1200.00]);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/sendText')
                && $request['chatId'] === '551199999999@c.us'
                && $request['session'] === 'default'
                && str_contains($request['text'], 'Aluguel')
                && str_contains($request['text'], '1.200,00')
                && $request->hasHeader('X-Api-Key', 'segredo');
        });

        $this->assertDatabaseHas('expense_notifications', [
            'user_id' => $user->id,
            'channel' => 'whatsapp',
        ]);
    }

    public function test_numero_sem_whatsapp_nao_vira_envio_nem_registro(): void
    {
        Mail::fake();
        config(['waha.url' => 'http://waha.local:3000']);

        Http::fake([
            '*/api/contacts/check-exists*' => Http::response(['numberExists' => false]),
            '*/api/sendText' => Http::response(['id' => 'abc']),
        ]);

        $user = $this->userWithEmail([
            'notify_by_email' => false,
            'notify_by_whatsapp' => true,
            'whatsapp_number' => '(11) 99999-9999',
        ]);
        $this->expenseFor($user, 2);

        $this->artisan('expenses:notify-due')->assertFailed();

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/api/sendText'));
        $this->assertSame(0, ExpenseNotification::count());
    }

    public function test_falha_no_whatsapp_nao_impede_o_email(): void
    {
        Mail::fake();
        config(['waha.url' => 'http://waha.local:3000']);

        Http::fake([
            '*/api/contacts/check-exists*' => Http::response(['numberExists' => true, 'chatId' => '55@c.us']),
            '*/api/sendText' => Http::response([], 500),
        ]);

        $user = $this->userWithEmail([
            'notify_by_email' => true,
            'notify_by_whatsapp' => true,
            'whatsapp_number' => '(11) 99999-9999',
        ]);
        $this->expenseFor($user, 2);

        $this->artisan('expenses:notify-due')->assertFailed();

        Mail::assertSent(DueExpensesMail::class);
        $this->assertDatabaseMissing('expense_notifications', ['channel' => 'whatsapp']);
        $this->assertDatabaseHas('expense_notifications', ['channel' => 'mail']);
    }

    public function test_whatsapp_desligado_no_sistema_nao_tenta_enviar(): void
    {
        Mail::fake();
        config(['waha.url' => null]);
        Http::fake();

        $user = $this->userWithEmail([
            'notify_by_email' => false,
            'notify_by_whatsapp' => true,
            'whatsapp_number' => '(11) 99999-9999',
        ]);
        $this->expenseFor($user, 2);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        Http::assertNothingSent();
    }
}
