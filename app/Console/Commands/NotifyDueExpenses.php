<?php

namespace App\Console\Commands;

use App\Services\DueExpenseNotifier;
use Illuminate\Console\Command;

class NotifyDueExpenses extends Command
{
    protected $signature = 'expenses:notify-due';

    protected $description = 'Avisa cada usuário sobre as despesas que estão perto de vencer';

    public function handle(DueExpenseNotifier $notifier): int
    {
        $summary = $notifier->notifyEveryone();

        $this->info("{$summary->messages()} mensagem(ns) enviada(s), cobrindo {$summary->expenses()} despesa(s).");

        foreach ($summary->failures() as $failure) {
            $this->warn("Falha no canal {$failure['channel']}: {$failure['reason']}");
        }

        return $summary->hasFailures() ? self::FAILURE : self::SUCCESS;
    }
}
