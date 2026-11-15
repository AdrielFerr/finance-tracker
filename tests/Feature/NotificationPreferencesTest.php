<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_configura_o_proprio_prazo_de_aviso(): void
    {
        $user = User::factory()->create(['notify_days_before' => 3]);

        $response = $this->actingAs($user)->put(route('profile.update-notifications'), [
            'notify_days_before' => 10,
            'notify_by_email' => '1',
            'notify_on_due_date' => '1',
        ]);

        $response->assertSessionHas('status', 'notification-preferences-updated');

        $user->refresh();
        $this->assertSame(10, $user->notify_days_before);
        $this->assertTrue($user->notify_by_email);
        $this->assertTrue($user->notify_on_due_date);
        $this->assertFalse($user->notify_when_overdue);
    }

    public function test_desmarcar_tudo_desliga_os_avisos(): void
    {
        $user = User::factory()->create([
            'notify_by_email' => true,
            'notify_on_due_date' => true,
        ]);

        $this->actingAs($user)->put(route('profile.update-notifications'), [
            'notify_days_before' => 5,
        ]);

        $user->refresh();
        $this->assertFalse($user->notify_by_email);
        $this->assertFalse($user->notify_by_whatsapp);
        $this->assertFalse($user->notify_on_due_date);
    }

    public function test_whatsapp_exige_numero(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.update-notifications'), [
            'notify_days_before' => 3,
            'notify_by_whatsapp' => '1',
        ]);

        $response->assertSessionHasErrors('whatsapp_number');
        $this->assertFalse($user->fresh()->notify_by_whatsapp);
    }

    public function test_numero_invalido_e_recusado(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.update-notifications'), [
            'notify_days_before' => 3,
            'notify_by_whatsapp' => '1',
            'whatsapp_number' => '123',
        ]);

        $response->assertSessionHasErrors('whatsapp_number');
    }

    public function test_numero_valido_e_aceito(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.update-notifications'), [
            'notify_days_before' => 3,
            'notify_by_whatsapp' => '1',
            'whatsapp_number' => '(11) 99999-9999',
        ]);

        $user->refresh();
        $this->assertTrue($user->notify_by_whatsapp);
        $this->assertSame('(11) 99999-9999', $user->whatsapp_number);
    }

    public function test_prazo_fora_do_limite_e_recusado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update-notifications'), ['notify_days_before' => 0])
            ->assertSessionHasErrors('notify_days_before');

        $this->actingAs($user)
            ->put(route('profile.update-notifications'), ['notify_days_before' => 999])
            ->assertSessionHasErrors('notify_days_before');
    }

    public function test_a_aba_de_avisos_aparece_no_perfil(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Avisos de vencimento');
        $response->assertSee(route('profile.update-notifications'));
    }

    public function test_visitante_nao_altera_preferencias(): void
    {
        $this->put(route('profile.update-notifications'), ['notify_days_before' => 5])
            ->assertRedirect(route('login'));
    }
}
