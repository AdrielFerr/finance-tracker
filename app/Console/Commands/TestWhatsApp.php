<?php

namespace App\Console\Commands;

use App\Services\WhatsApp\WahaClient;
use App\Services\WhatsApp\WahaSettings;
use App\Services\WhatsApp\WhatsAppDeliveryException;
use Illuminate\Console\Command;

class TestWhatsApp extends Command
{
    protected $signature = 'whatsapp:test {phone : Número com DDD, ex: 11999999999}';

    protected $description = 'Confere a conexão com o WAHA e manda uma mensagem de teste';

    private const CONNECTED_STATUS = 'WORKING';

    public function handle(WahaClient $waha, WahaSettings $settings): int
    {
        if (!$waha->isConfigured()) {
            $this->error('O endereço do servidor não está definido. Configure em Administração > Configurações.');

            return self::FAILURE;
        }

        $this->line('Servidor: ' . $settings->url());
        $this->line('Sessão: ' . $settings->session());

        if (!$waha->isReachable()) {
            $this->error('O servidor não respondeu. Confira o endereço e se o serviço está no ar.');

            return self::FAILURE;
        }

        $this->info('Servidor alcançado.');

        if (!$settings->hasApiKey()) {
            $this->error('Nenhuma chave de acesso guardada. Informe em Administração > Configurações.');

            return self::FAILURE;
        }

        return $this->checkSession($waha) && $this->sendTestMessage($waha)
            ? self::SUCCESS
            : self::FAILURE;
    }

    private function checkSession(WahaClient $waha): bool
    {
        try {
            $status = $waha->sessionStatus();
        } catch (WhatsAppDeliveryException $e) {
            $this->error('Não consegui falar com o WAHA: ' . $e->getMessage());

            return false;
        }

        if ($status !== self::CONNECTED_STATUS) {
            $this->error("A sessão está em \"{$status}\", e não em \"" . self::CONNECTED_STATUS . '".');
            $this->line('Se estiver em SCAN_QR_CODE, abra o painel do WAHA e leia o QR com o celular.');

            return false;
        }

        $this->info('Sessão conectada.');

        return true;
    }

    private function sendTestMessage(WahaClient $waha): bool
    {
        $phone = (string) $this->argument('phone');

        try {
            $waha->sendText($phone, 'Teste do FinanceTracker. Se você recebeu isto, os avisos de vencimento estão prontos.');
        } catch (WhatsAppDeliveryException $e) {
            $this->error('Não consegui enviar: ' . $e->getMessage());

            return false;
        }

        $this->info('Mensagem enviada. Confira o WhatsApp do número informado.');

        return true;
    }
}
