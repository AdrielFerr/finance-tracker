<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Notifications\Contracts\NotificationChannel;
use App\Notifications\DueKind;
use App\Services\NotificationFeed;
use Illuminate\Support\Collection;

class InAppChannel implements NotificationChannel
{
    public function __construct(private readonly NotificationFeed $feed) {}

    public function name(): string
    {
        return 'in_app';
    }

    public function isEnabledFor(User $user): bool
    {
        return $user->notify_in_app;
    }

    public function send(User $user, Collection $expenses, DueKind $kind): void
    {
        $this->feed->push(
            $user,
            $kind->title(),
            $this->summarize($expenses),
            route('expenses.index'),
            $this->levelFor($kind),
        );
    }

    /**
     * @param Collection<int, \App\Models\Expense> $expenses
     */
    private function summarize(Collection $expenses): string
    {
        $total = 'R$ ' . number_format($expenses->sum('amount'), 2, ',', '.');

        if ($expenses->count() === 1) {
            return $expenses->first()->description . ' — ' . $total;
        }

        return $expenses->count() . ' despesas, somando ' . $total
            . '. A primeira é ' . $expenses->first()->description . '.';
    }

    private function levelFor(DueKind $kind): string
    {
        return match ($kind) {
            DueKind::DueSoon => 'info',
            DueKind::DueToday => 'warning',
            DueKind::Overdue => 'danger',
        };
    }
}
