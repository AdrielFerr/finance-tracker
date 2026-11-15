<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWahaSettingsRequest;
use App\Services\WhatsApp\WahaClient;
use App\Services\WhatsApp\WahaSettings;
use App\Services\WhatsApp\WhatsAppDeliveryException;
use Illuminate\Http\RedirectResponse;

class WhatsAppConfigController extends Controller
{
    private const CONNECTED_STATUS = 'WORKING';

    public function __construct(
        private WahaSettings $settings,
        private WahaClient $waha,
    ) {}

    public function update(UpdateWahaSettingsRequest $request): RedirectResponse
    {
        $this->settings->save(
            $request->input('waha_url'),
            $request->input('waha_session'),
            $request->input('waha_api_key'),
        );

        return back()->with('success', 'Configurações do WhatsApp salvas.');
    }

    public function forgetKey(): RedirectResponse
    {
        $this->settings->forgetApiKey();

        return back()->with('success', 'Chave de acesso removida.');
    }

    public function test(): RedirectResponse
    {
        if (!$this->settings->isConfigured()) {
            return back()->with('error', 'Informe o endereço do servidor antes de testar.');
        }

        if (!$this->waha->isReachable()) {
            return back()->with('error', 'Não consegui alcançar o servidor no endereço informado.');
        }

        return back()->with($this->checkSession());
    }

    /**
     * @return array<string, string>
     */
    private function checkSession(): array
    {
        try {
            $status = $this->waha->sessionStatus();
        } catch (WhatsAppDeliveryException $e) {
            return ['error' => $e->getMessage()];
        }

        if ($status !== self::CONNECTED_STATUS) {
            return ['error' => "Servidor respondeu, mas a sessão está em \"{$status}\". Leia o QR no painel do WAHA."];
        }

        return ['success' => 'Tudo certo: servidor alcançado e sessão conectada.'];
    }
}
