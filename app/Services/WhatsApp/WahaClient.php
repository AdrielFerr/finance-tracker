<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class WahaClient
{
    private const REQUEST_TIMEOUT = 10;

    public function __construct(
        private readonly BrazilianPhone $phones,
        private readonly WahaSettings $settings,
    ) {}

    public function isConfigured(): bool
    {
        return $this->settings->isConfigured();
    }

    public function isReachable(): bool
    {
        try {
            return $this->request()->get('/ping')->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function sessionStatus(): string
    {
        $this->guardConfigured();

        $response = $this->request()->get('/api/sessions/' . $this->settings->session());

        if (!$response->successful()) {
            throw WhatsAppDeliveryException::requestFailed($response->status());
        }

        return (string) $response->json('status', 'UNKNOWN');
    }

    public function sendText(string $phone, string $text): void
    {
        $this->guardConfigured();

        $response = $this->request()->post('/api/sendText', [
            'session' => $this->settings->session(),
            'chatId' => $this->resolveChatId($phone),
            'text' => $text,
        ]);

        if (!$response->successful()) {
            throw WhatsAppDeliveryException::requestFailed($response->status());
        }
    }

    private function guardConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw WhatsAppDeliveryException::notConfigured();
        }
    }

    private function resolveChatId(string $phone): string
    {
        $number = $this->phones->toInternational($phone);

        if ($number === null) {
            throw WhatsAppDeliveryException::invalidNumber();
        }

        $response = $this->request()->get('/api/contacts/check-exists', [
            'phone' => $number,
            'session' => $this->settings->session(),
        ]);

        if (!$response->successful()) {
            return $this->defaultChatId($number);
        }

        if ($response->json('numberExists') !== true) {
            throw WhatsAppDeliveryException::numberNotOnWhatsApp();
        }

        return $response->json('chatId') ?: $this->defaultChatId($number);
    }

    private function defaultChatId(string $number): string
    {
        return $number . '@c.us';
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) $this->settings->url(), '/'))
            ->withHeaders(['X-Api-Key' => (string) $this->settings->apiKey()])
            ->timeout((int) (config('waha.timeout') ?: self::REQUEST_TIMEOUT))
            ->acceptJson();
    }
}
