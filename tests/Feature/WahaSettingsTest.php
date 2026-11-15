<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\WhatsApp\WahaSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WahaSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    }

    private function settings(): WahaSettings
    {
        return app(WahaSettings::class);
    }

    public function test_super_admin_salva_as_configuracoes(): void
    {
        $response = $this->actingAs($this->superAdmin())
            ->put(route('admin.system-config.whatsapp.update'), [
                'waha_url' => 'https://apiwaha.exemplo.com.br',
                'waha_session' => 'default',
                'waha_api_key' => 'chave-secreta-123',
            ]);

        $response->assertSessionHas('success');

        $this->assertSame('https://apiwaha.exemplo.com.br', $this->settings()->url());
        $this->assertSame('default', $this->settings()->session());
        $this->assertSame('chave-secreta-123', $this->settings()->apiKey());
    }

    public function test_a_chave_nao_fica_em_texto_puro_no_banco(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('admin.system-config.whatsapp.update'), [
                'waha_url' => 'https://apiwaha.exemplo.com.br',
                'waha_session' => 'default',
                'waha_api_key' => 'chave-secreta-123',
            ]);

        $stored = SystemSetting::where('key', 'waha_api_key')->value('value');

        $this->assertNotSame('chave-secreta-123', $stored);
        $this->assertStringNotContainsString('chave-secreta-123', $stored);
        $this->assertSame('chave-secreta-123', Crypt::decryptString($stored));
    }

    public function test_a_chave_nunca_aparece_na_tela(): void
    {
        $this->settings()->save('https://apiwaha.exemplo.com.br', 'default', 'chave-secreta-123');

        $response = $this->actingAs($this->superAdmin())->get(route('admin.system-config.index'));

        $response->assertOk();
        $response->assertDontSee('chave-secreta-123');
        $response->assertSee('Chave guardada');
    }

    public function test_campo_em_branco_mantem_a_chave_atual(): void
    {
        $this->settings()->save('https://antigo.exemplo.com.br', 'default', 'chave-original');

        $this->actingAs($this->superAdmin())
            ->put(route('admin.system-config.whatsapp.update'), [
                'waha_url' => 'https://novo.exemplo.com.br',
                'waha_session' => 'default',
                'waha_api_key' => '',
            ]);

        $this->assertSame('https://novo.exemplo.com.br', $this->settings()->url());
        $this->assertSame('chave-original', $this->settings()->apiKey());
    }

    public function test_remover_a_chave_apaga_de_verdade(): void
    {
        $this->settings()->save('https://apiwaha.exemplo.com.br', 'default', 'chave-original');

        $this->actingAs($this->superAdmin())
            ->delete(route('admin.system-config.whatsapp.forget-key'))
            ->assertSessionHas('success');

        $this->assertFalse($this->settings()->hasApiKey());
    }

    public function test_endereco_invalido_e_recusado(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('admin.system-config.whatsapp.update'), [
                'waha_url' => 'isso nao e uma url',
                'waha_session' => 'default',
            ])
            ->assertSessionHasErrors('waha_url');
    }

    public function test_sessao_com_caractere_estranho_e_recusada(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('admin.system-config.whatsapp.update'), [
                'waha_url' => 'https://apiwaha.exemplo.com.br',
                'waha_session' => 'nome com espaco/barra',
            ])
            ->assertSessionHasErrors('waha_session');
    }

    public function test_usuario_comum_nao_altera_a_configuracao(): void
    {
        $comum = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $this->actingAs($comum)
            ->put(route('admin.system-config.whatsapp.update'), [
                'waha_url' => 'https://invasor.exemplo.com.br',
                'waha_session' => 'default',
                'waha_api_key' => 'chave-do-invasor',
            ])
            ->assertForbidden();

        $this->assertNotSame('https://invasor.exemplo.com.br', $this->settings()->url());
        $this->assertNotSame('chave-do-invasor', $this->settings()->apiKey());
        $this->assertDatabaseMissing('system_settings', ['key' => 'waha_url']);
    }

    public function test_visitante_nao_altera_a_configuracao(): void
    {
        $this->put(route('admin.system-config.whatsapp.update'), [
            'waha_url' => 'https://invasor.exemplo.com.br',
        ])->assertRedirect(route('login'));
    }

    public function test_o_teste_de_conexao_avisa_quando_tudo_esta_certo(): void
    {
        $this->settings()->save('https://apiwaha.exemplo.com.br', 'default', 'chave');

        Http::fake([
            '*/ping' => Http::response(['message' => 'pong']),
            '*/api/sessions/*' => Http::response(['status' => 'WORKING']),
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.system-config.whatsapp.test'))
            ->assertSessionHas('success');
    }

    public function test_o_teste_de_conexao_avisa_quando_o_qr_falta(): void
    {
        $this->settings()->save('https://apiwaha.exemplo.com.br', 'default', 'chave');

        Http::fake([
            '*/ping' => Http::response(['message' => 'pong']),
            '*/api/sessions/*' => Http::response(['status' => 'SCAN_QR_CODE']),
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.system-config.whatsapp.test'))
            ->assertSessionHas('error');
    }

    public function test_o_teste_de_conexao_avisa_servidor_fora_do_ar(): void
    {
        $this->settings()->save('https://apiwaha.exemplo.com.br', 'default', 'chave');

        Http::fake(['*' => Http::response([], 500)]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.system-config.whatsapp.test'))
            ->assertSessionHas('error');
    }

    public function test_configuracao_do_banco_vence_a_do_env(): void
    {
        config(['waha.url' => 'https://do-env.exemplo.com.br', 'waha.api_key' => 'chave-do-env']);

        $this->settings()->save('https://do-banco.exemplo.com.br', 'outra', 'chave-do-banco');

        $this->assertSame('https://do-banco.exemplo.com.br', $this->settings()->url());
        $this->assertSame('chave-do-banco', $this->settings()->apiKey());
        $this->assertSame('outra', $this->settings()->session());
    }

    public function test_sem_nada_no_banco_cai_no_env(): void
    {
        config(['waha.url' => 'https://do-env.exemplo.com.br', 'waha.api_key' => 'chave-do-env']);

        $this->assertSame('https://do-env.exemplo.com.br', $this->settings()->url());
        $this->assertSame('chave-do-env', $this->settings()->apiKey());
    }

    public function test_chave_ilegivel_e_detectada_em_vez_de_quebrar(): void
    {
        SystemSetting::set('waha_api_key', 'lixo-que-nao-descriptografa');
        SystemSetting::clearCache();

        $this->assertNull($this->settings()->apiKey());
        $this->assertTrue($this->settings()->storedApiKeyIsUnreadable());
    }
}
