<?php

namespace App\Notifications\Contracts;

use App\Models\User;
use App\Notifications\DueKind;
use Illuminate\Support\Collection;

interface NotificationChannel
{
    public function name(): string;

    public function isEnabledFor(User $user): bool;

    /**
     * @param Collection<int, \App\Models\Expense> $expenses
     */
    public function send(User $user, Collection $expenses, DueKind $kind): void;
}
