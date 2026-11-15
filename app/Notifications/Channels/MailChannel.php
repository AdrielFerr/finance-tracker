<?php

namespace App\Notifications\Channels;

use App\Mail\DueExpensesMail;
use App\Models\User;
use App\Notifications\Contracts\NotificationChannel;
use App\Notifications\DueKind;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class MailChannel implements NotificationChannel
{
    public function name(): string
    {
        return 'mail';
    }

    public function isEnabledFor(User $user): bool
    {
        return $user->notify_by_email && filled($user->email);
    }

    public function send(User $user, Collection $expenses, DueKind $kind): void
    {
        Mail::to($user->email)->send(new DueExpensesMail($user, $expenses, $kind));
    }
}
