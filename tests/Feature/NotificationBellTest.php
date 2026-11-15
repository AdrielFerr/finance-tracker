<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    private function feed(): NotificationFeed
    {
        return app(NotificationFeed::class);
    }

    private function userWithBell(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'is_active' => true,
            'notify_in_app' => true,
            'notify_by_email' => false,
            'notify_by_whatsapp' => false,
            'notify_days_before' => 3,
            'notify_on_due_date' => true,
        ], $attributes));
    }

    public function test_despesa_a_vencer_vira_notificacao_no_sininho(): void
    {
        Mail::fake();

        $user = $this->userWithBell();
        Expense::factory()->for($user)->for(Category::factory()->for($user))->dueIn(2)
            ->create(['description' => 'Aluguel', 'amount' => 1850.00]);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        $this->assertSame(1, $this->feed()->unreadCountFor($user));

        $notification = UserNotification::forUser($user->id)->first();
        $this->assertSame('Despesas chegando no vencimento', $notification->title);
        $this->assertStringContainsString('Aluguel', $notification->body);
        $this->assertStringContainsString('1.850,00', $notification->body);
    }

    public function test_varias_despesas_viram_um_resumo_so(): void
    {
        Mail::fake();

        $user = $this->userWithBell();
        $categoria = Category::factory()->for($user)->create();

        Expense::factory()->for($user)->for($categoria)->dueIn(2)->create(['description' => 'Aluguel', 'amount' => 1000.00]);
        Expense::factory()->for($user)->for($categoria)->dueIn(2)->create(['description' => 'Luz', 'amount' => 200.00]);

        $this->artisan('expenses:notify-due')->assertSuccessful();

        $this->assertSame(1, UserNotification::count());
        $this->assertStringContainsString('2 despesas', UserNotification::first()->body);
        $this->assertStringContainsString('1.200,00', UserNotification::first()->body);
    }

    public function test_quem_desligou_o_sininho_nao_recebe(): void
    {
        Mail::fake();

        $user = $this->userWithBell(['notify_in_app' => false]);
        Expense::factory()->for($user)->for(Category::factory()->for($user))->dueIn(2)->create();

        $this->artisan('expenses:notify-due')->assertSuccessful();

        $this->assertSame(0, UserNotification::count());
    }

    public function test_o_contador_aparece_no_layout(): void
    {
        $user = $this->userWithBell();
        $this->feed()->push($user, 'Conta vencendo', 'Aluguel — R$ 1.850,00');

        $response = $this->actingAs($user)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertSee('Conta vencendo');
        $response->assertSee('Aluguel — R$ 1.850,00');
    }

    public function test_abrir_a_notificacao_marca_como_lida(): void
    {
        $user = $this->userWithBell();
        $notification = $this->feed()->push($user, 'Conta vencendo', 'Aluguel', route('expenses.index'));

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('expenses.index'));

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0, $this->feed()->unreadCountFor($user));
    }

    public function test_marcar_todas_como_lidas(): void
    {
        $user = $this->userWithBell();
        $this->feed()->push($user, 'Uma', 'corpo');
        $this->feed()->push($user, 'Outra', 'corpo');

        $this->assertSame(2, $this->feed()->unreadCountFor($user));

        $this->actingAs($user)->post(route('notifications.read-all'));

        $this->assertSame(0, $this->feed()->unreadCountFor($user));
    }

    public function test_usuario_nao_ve_notificacao_de_outro(): void
    {
        $dono = $this->userWithBell();
        $intruso = $this->userWithBell();

        $this->feed()->push($dono, 'Segredo do dono', 'corpo privado');

        $response = $this->actingAs($intruso)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertDontSee('Segredo do dono');
        $this->assertSame(0, $this->feed()->unreadCountFor($intruso));
    }

    public function test_usuario_nao_marca_como_lida_notificacao_de_outro(): void
    {
        $dono = $this->userWithBell();
        $intruso = $this->userWithBell();

        $notification = $this->feed()->push($dono, 'Do dono', 'corpo');

        $this->actingAs($intruso)->get(route('notifications.open', $notification));

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_marcar_todas_nao_afeta_outro_usuario(): void
    {
        $dono = $this->userWithBell();
        $outro = $this->userWithBell();

        $this->feed()->push($dono, 'Do dono', 'corpo');
        $this->feed()->push($outro, 'Do outro', 'corpo');

        $this->actingAs($dono)->post(route('notifications.read-all'));

        $this->assertSame(0, $this->feed()->unreadCountFor($dono));
        $this->assertSame(1, $this->feed()->unreadCountFor($outro));
    }

    public function test_sininho_nao_repete_o_mesmo_aviso(): void
    {
        Mail::fake();

        $user = $this->userWithBell();
        Expense::factory()->for($user)->for(Category::factory()->for($user))->dueIn(2)->create();

        $this->artisan('expenses:notify-due')->assertSuccessful();
        $this->artisan('expenses:notify-due')->assertSuccessful();

        $this->assertSame(1, UserNotification::count());
    }

    public function test_visitante_nao_abre_notificacao(): void
    {
        $user = $this->userWithBell();
        $notification = $this->feed()->push($user, 'Titulo', 'corpo');

        $this->get(route('notifications.open', $notification))->assertRedirect(route('login'));
    }
}
