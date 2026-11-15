<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Models\User;
use App\Notifications\DueKind;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DueExpenseRepository
{
    private const OPEN_STATUSES = ['pending', 'overdue'];

    /**
     * @return Collection<int, Expense>
     */
    public function notYetNotified(User $user, DueKind $kind, string $channel): Collection
    {
        return $this->baseQuery($user, $kind)
            ->whereDoesntHave('notifications', function ($query) use ($channel, $kind) {
                $query->where('channel', $channel)->where('kind', $kind->value);
            })
            ->with('category')
            ->orderBy('due_date')
            ->get();
    }

    private function baseQuery(User $user, DueKind $kind)
    {
        $today = CarbonImmutable::today();

        $query = Expense::query()
            ->where('user_id', $user->id)
            ->whereIn('status', self::OPEN_STATUSES);

        return match ($kind) {
            DueKind::DueSoon => $query
                ->whereDate('due_date', '>', $today)
                ->whereDate('due_date', '<=', $today->addDays(max(1, $user->notify_days_before))),
            DueKind::DueToday => $query->whereDate('due_date', $today),
            DueKind::Overdue => $query->whereDate('due_date', '<', $today),
        };
    }
}
