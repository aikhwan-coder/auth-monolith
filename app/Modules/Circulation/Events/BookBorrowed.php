<?php

namespace Modules\Circulation\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Circulation\Models\Loan;

/**
 * Domain event: ditembakkan setelah peminjaman berhasil (transaction sudah commit).
 */
class BookBorrowed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Loan $loan
    ) {}
}
