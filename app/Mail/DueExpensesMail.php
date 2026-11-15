<?php

namespace App\Mail;

use App\Models\User;
use App\Notifications\DueKind;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DueExpensesMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param Collection<int, \App\Models\Expense> $expenses
     */
    public function __construct(
        public readonly User $user,
        public readonly Collection $expenses,
        public readonly DueKind $kind,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->kind->title());
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.due-expenses',
            with: [
                'total' => $this->expenses->sum('amount'),
            ],
        );
    }
}
