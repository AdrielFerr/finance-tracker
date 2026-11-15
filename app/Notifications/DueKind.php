<?php

namespace App\Notifications;

enum DueKind: string
{
    case DueSoon = 'due_soon';
    case DueToday = 'due_today';
    case Overdue = 'overdue';

    public function title(): string
    {
        return match ($this) {
            self::DueSoon => 'Despesas chegando no vencimento',
            self::DueToday => 'Despesas que vencem hoje',
            self::Overdue => 'Despesas vencidas',
        };
    }

    public function callToAction(): string
    {
        return match ($this) {
            self::DueSoon => 'Dá tempo de se organizar:',
            self::DueToday => 'Vencem hoje:',
            self::Overdue => 'Já passaram do prazo:',
        };
    }
}
