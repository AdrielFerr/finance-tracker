<?php

namespace App\Services\WhatsApp;

use App\Models\SystemSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class WahaSettings
{
    private const URL = 'waha_url';
    private const API_KEY = 'waha_api_key';
    private const SESSION = 'waha_session';
    private const DEFAULT_SESSION = 'default';

    public function url(): ?string
    {
        return SystemSetting::get(self::URL) ?: config('waha.url');
    }

    public function apiKey(): ?string
    {
        $stored = SystemSetting::get(self::API_KEY);

        if (blank($stored)) {
            return config('waha.api_key');
        }

        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException) {
            return null;
        }
    }

    public function session(): string
    {
        return SystemSetting::get(self::SESSION)
            ?: (config('waha.session') ?: self::DEFAULT_SESSION);
    }

    public function isConfigured(): bool
    {
        return filled($this->url());
    }

    public function hasApiKey(): bool
    {
        return filled($this->apiKey());
    }

    public function storedApiKeyIsUnreadable(): bool
    {
        return filled(SystemSetting::get(self::API_KEY)) && !$this->hasApiKey();
    }

    public function save(?string $url, ?string $session, ?string $apiKey = null): void
    {
        SystemSetting::set(self::URL, $url ?: null);
        SystemSetting::set(self::SESSION, $session ?: self::DEFAULT_SESSION);

        if (filled($apiKey)) {
            SystemSetting::set(self::API_KEY, Crypt::encryptString($apiKey));
        }

        SystemSetting::clearCache();
    }

    public function forgetApiKey(): void
    {
        SystemSetting::set(self::API_KEY, null);
        SystemSetting::clearCache();
    }
}
