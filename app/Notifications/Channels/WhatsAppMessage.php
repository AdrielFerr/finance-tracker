<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Notifications\DueKind;
use Illuminate\Support\Collection;

final class WhatsAppMessage
{
    /**
     * @param Collection<int, \App\Models\Expense> $expenses
     */
    public function build(User $user, Collection $expenses, DueKind $kind): string
    {
        $lines = [
            '*' . $kind->title() . '*',
            '',
            'Olá, ' . $user->name . '. ' . $kind->callToAction(),
            '',
        ];

        foreach ($expenses as $expense) {
            $lines[] = sprintf(
                '• %s — R$ %s (vence %s)',
                $expense->description,
                number_format($expense->amount, 2, ',', '.'),
                $expense->due_date->format('d/m')
            );
        }

        $lines[] = '';
        $lines[] = 'Total: R$ ' . number_format($expenses->sum('amount'), 2, ',', '.');

        return implode("\n", $lines);
    }
}
