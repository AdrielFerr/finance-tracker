<?php

namespace App\Services\WhatsApp;

use RuntimeException;

class WhatsAppDeliveryException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('O envio por WhatsApp não está configurado (WAHA_URL ausente).');
    }

    public static function invalidNumber(): self
    {
        return new self('O número de WhatsApp cadastrado não é válido.');
    }

    public static function numberNotOnWhatsApp(): self
    {
        return new self('O número cadastrado não tem WhatsApp.');
    }

    public static function requestFailed(int $status): self
    {
        return new self(match (true) {
            $status === 401 || $status === 403 => 'O WAHA recusou a chave de acesso (WAHA_API_KEY).',
            $status === 404 => 'A sessão informada em WAHA_SESSION não existe no servidor.',
            $status >= 500 => "O servidor do WAHA respondeu com erro {$status}. Pode ser o proxy na frente dele ou o serviço reiniciando.",
            default => "O WAHA respondeu com erro {$status}.",
        });
    }

    public static function unreachable(): self
    {
        return new self('Não consegui alcançar o servidor do WAHA no endereço de WAHA_URL.');
    }
}
