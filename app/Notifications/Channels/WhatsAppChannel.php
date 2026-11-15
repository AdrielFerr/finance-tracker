<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Notifications\Contracts\NotificationChannel;
use App\Notifications\DueKind;
use App\Services\WhatsApp\WahaClient;
use Illuminate\Support\Collection;

class WhatsAppChannel implements NotificationChannel
{
    public function __construct(
        private readonly WahaClient $waha,
        private readonly WhatsAppMessage $message,
    ) {}

    public function name(): string
    {
        return 'whatsapp';
    }

    public function isEnabledFor(User $user): bool
    {
        return $user->notify_by_whatsapp
            && filled($user->whatsapp_number)
            && $this->waha->isConfigured();
    }

    public function send(User $user, Collection $expenses, DueKind $kind): void
    {
        $this->waha->sendText(
            $user->whatsapp_number,
            $this->message->build($user, $expenses, $kind)
        );
    }
}
