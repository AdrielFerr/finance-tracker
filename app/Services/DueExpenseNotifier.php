<?php

namespace App\Services;

use App\Models\ExpenseNotification;
use App\Models\User;
use App\Notifications\Contracts\NotificationChannel;
use App\Notifications\DueKind;
use App\Repositories\DueExpenseRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DueExpenseNotifier
{
    /**
     * @param array<int, NotificationChannel> $channels
     */
    public function __construct(
        private DueExpenseRepository $expenses,
        private array $channels,
    ) {}

    public function notifyEveryone(): NotificationSummary
    {
        $summary = new NotificationSummary();

        User::where('is_active', true)
            ->where(fn ($query) => $query->where('notify_in_app', true)
                ->orWhere('notify_by_email', true)
                ->orWhere('notify_by_whatsapp', true))
            ->cursor()
            ->each(fn (User $user) => $this->notifyUser($user, $summary));

        return $summary;
    }

    public function notifyUser(User $user, ?NotificationSummary $summary = null): NotificationSummary
    {
        $summary ??= new NotificationSummary();

        foreach ($this->kindsFor($user) as $kind) {
            foreach ($this->channels as $channel) {
                $this->deliver($channel, $user, $kind, $summary);
            }
        }

        return $summary;
    }

    private function deliver(NotificationChannel $channel, User $user, DueKind $kind, NotificationSummary $summary): void
    {
        if (!$channel->isEnabledFor($user)) {
            return;
        }

        $expenses = $this->expenses->notYetNotified($user, $kind, $channel->name());

        if ($expenses->isEmpty()) {
            return;
        }

        try {
            $channel->send($user, $expenses, $kind);
            $this->record($user, $expenses, $channel->name(), $kind);
            $summary->addSent($channel->name(), $expenses->count());
        } catch (\Throwable $e) {
            $summary->addFailure($channel->name(), $e->getMessage());
        }
    }

    /**
     * @return array<int, DueKind>
     */
    private function kindsFor(User $user): array
    {
        $kinds = [DueKind::DueSoon];

        if ($user->notify_on_due_date) {
            $kinds[] = DueKind::DueToday;
        }

        if ($user->notify_when_overdue) {
            $kinds[] = DueKind::Overdue;
        }

        return $kinds;
    }

    /**
     * @param Collection<int, \App\Models\Expense> $expenses
     */
    private function record(User $user, Collection $expenses, string $channel, DueKind $kind): void
    {
        DB::transaction(function () use ($user, $expenses, $channel, $kind) {
            foreach ($expenses as $expense) {
                ExpenseNotification::create([
                    'user_id' => $user->id,
                    'expense_id' => $expense->id,
                    'channel' => $channel,
                    'kind' => $kind->value,
                    'sent_at' => now(),
                ]);
            }
        });
    }
}
