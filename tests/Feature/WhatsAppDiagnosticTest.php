<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppDiagnosticTest extends TestCase
{
    use RefreshDatabase;

    private function configureWaha(?string $apiKey = 'segredo'): void
    {
        config([
            'waha.url' => 'https://waha.local',
            'waha.api_key' => $apiKey,
            'waha.session' => 'default',
        ]);
    }

    public function test_avisa_quando_a_url_nao_esta_configurada(): void
    {
        config(['waha.url' => null]);

        $this->artisan('whatsapp:test', ['phone' => '11999999999'])
            ->expectsOutputToContain('endereço do servidor não está definido')
            ->assertFailed();
    }

    public function test_avisa_quando_o_servidor_nao_responde(): void
    {
        $this->configureWaha();

        Http::fake(['*/ping' => Http::response([], 500)]);

        $this->artisan('whatsapp:test', ['phone' => '11999999999'])
            ->expectsOutputToContain('O servidor não respondeu')
            ->assertFailed();
    }

    public function test_avisa_quando_a_chave_esta_vazia(): void
    {
        $this->configureWaha(apiKey: '');

        Http::fake(['*/ping' => Http::response(['message' => 'pong'])]);

        $this->artisan('whatsapp:test', ['phone' => '11999999999'])
            ->expectsOutputToContain('Nenhuma chave de acesso guardada')
            ->assertFailed();
    }

    public function test_avisa_quando_a_chave_e_recusada(): void
    {
        $this->configureWaha();

        Http::fake([
            '*/ping' => Http::response(['message' => 'pong']),
            '*/api/sessions/*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $this->artisan('whatsapp:test', ['phone' => '11999999999'])
            ->expectsOutputToContain('recusou a chave de acesso')
            ->assertFailed();
    }

    public function test_avisa_quando_a_sessao_nao_existe(): void
    {
        $this->configureWaha();

        Http::fake([
            '*/ping' => Http::response(['message' => 'pong']),
            '*/api/sessions/*' => Http::response([], 404),
        ]);

        $this->artisan('whatsapp:test', ['phone' => '11999999999'])
            ->expectsOutputToContain('WAHA_SESSION não existe')
            ->assertFailed();
    }

    public function test_explica_erro_de_servidor_sem_culpar_a_configuracao(): void
    {
        $this->configureWaha();

        Http::fake([
            '*/ping' => Http::response(['message' => 'pong']),
            '*/api/sessions/*' => Http::response([], 502),
        ]);

        $this->artisan('whatsapp:test', ['phone' => '11999999999'])
            ->expectsOutputToContain('erro 502')
            ->assertFailed();
    }

    public function test_avisa_quando_o_qr_ainda_nao_foi_lido(): void
    {
        $this->configureWaha();

        Http::fake([
            '*/ping' => Http::response(['message' => 'pong']),
            '*/api/sessions/*' => Http::response(['status' => 'SCAN_QR_CODE']),
        ]);

        $this->artisan('whatsapp:test', ['phone' => '11999999999'])
            ->expectsOutputToContain('SCAN_QR_CODE')
            ->assertFailed();
    }

    public function test_envia_mensagem_quando_esta_tudo_certo(): void
    {
        $this->configureWaha();

        Http::fake([
            '*/ping' => Http::response(['message' => 'pong']),
            '*/api/sessions/*' => Http::response(['status' => 'WORKING']),
            '*/api/contacts/check-exists*' => Http::response(['numberExists' => true, 'chatId' => '5511999999999@c.us']),
            '*/api/sendText' => Http::response(['id' => 'msg-1']),
        ]);

        $this->artisan('whatsapp:test', ['phone' => '11999999999'])
            ->expectsOutputToContain('Mensagem enviada')
            ->assertSuccessful();

        Http::assertSent(fn ($request) => str_contains($request->url(), '/api/sendText')
            && $request['chatId'] === '5511999999999@c.us');
    }
}
