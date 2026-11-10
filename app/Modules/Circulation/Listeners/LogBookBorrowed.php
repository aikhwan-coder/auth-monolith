<?php

namespace Modules\Circulation\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Circulation\Events\BookBorrowed;

class LogBookBorrowed
{
    public function handle(BookBorrowed $event): void
    {
        $loan = $event->loan;

        Log::info('Buku dipinjam', [
            'loan_number' => $loan->loan_number,
            'member' => $loan->member_name,
            'items' => $loan->details
                ->map(fn ($d) => "{$d->book_title} x{$d->quantity}")
                ->all(),
            'due_at' => $loan->due_at->toDateString(),
        ]);
    }
}
