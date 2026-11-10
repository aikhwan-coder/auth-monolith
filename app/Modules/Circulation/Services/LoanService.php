<?php

namespace Modules\Circulation\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Circulation\Events\BookBorrowed;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Models\LoanDetail;
use Modules\Shared\Contracts\BookServiceInterface;
use Modules\Shared\Contracts\MemberServiceInterface;
use Modules\Shared\Exceptions\InsufficientStockException;
use Modules\Shared\ValueObjects\Denda;

class LoanService
{
    public function __construct(
        private BookServiceInterface $bookService,
        private MemberServiceInterface $memberService,
    ) {}

    /**
     * Peminjaman = satu operasi atomik:
     * validasi anggota -> buat Loan -> buat LoanDetail (snapshot) -> kurangi stok -> update status anggota.
     * Jika ada yang gagal (mis. stok kurang) SEMUANYA di-rollback.
     *
     * @param  list<array{book_id: int, quantity: int}>  $items
     */
    public function borrow(int $memberId, array $items, int $days = 7): Loan
    {
        // Gabungkan buku yang sama: [book_id => total quantity]
        $requested = collect($items)
            ->groupBy(fn (array $item) => (int) $item['book_id'])
            ->map(fn ($group) => (int) $group->sum('quantity'));

        // Invariant aggregate: peminjaman minimal 1 buku
        if ($requested->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Peminjaman minimal 1 buku.',
            ]);
        }

        $loan = DB::transaction(function () use ($memberId, $requested, $days): Loan {
            $member = $this->memberService->getMemberForLoan($memberId);

            if (! $member || ! $member['can_borrow']) {
                throw ValidationException::withMessages([
                    'member_id' => 'Anggota tidak ditemukan, di-suspend, atau keanggotaan sudah kedaluwarsa.',
                ]);
            }

            $loan = Loan::create([
                'member_id' => $member['id'],
                'member_name' => $member['name'],
                'loan_number' => 'LN-'.Str::upper(Str::random(10)),
                'borrowed_at' => now(),
                'due_at' => now()->addDays($days),
                'status' => Loan::STATUS_BORROWED,
                'fine_amount' => 0,
            ]);

            foreach ($requested as $bookId => $quantity) {
                $book = $this->bookService->getBookForLoan($bookId);

                if (! $book || ! $book['is_active']) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu buku tidak ditemukan atau sudah tidak aktif.',
                    ]);
                }

                if ($book['stock'] < $quantity) {
                    throw new InsufficientStockException($book['title'], $book['stock'], $quantity);
                }

                // Snapshot judul & ISBN supaya riwayat tidak berubah bila data buku diedit
                LoanDetail::create([
                    'loan_id' => $loan->id,
                    'book_id' => $book['id'],
                    'book_title' => $book['title'],
                    'isbn' => $book['isbn'],
                    'quantity' => $quantity,
                ]);

                if (! $this->bookService->reduceStock($book['id'], $quantity)) {
                    $current = $this->bookService->getBookForLoan($book['id']) ?? $book;

                    throw new InsufficientStockException($current['title'], $current['stock'], $quantity);
                }
            }

            $this->memberService->markAsBorrowing($member['id']);

            return $loan->load('details');
        });

        // Domain event: hanya ditembakkan jika transaction berhasil di-commit
        event(new BookBorrowed($loan));

        return $loan;
    }

    public function returnLoan(Loan $loan): Loan
    {
        return DB::transaction(function () use ($loan): Loan {
            $locked = Loan::query()->lockForUpdate()->findOrFail($loan->id);

            if ($locked->isReturned()) {
                throw ValidationException::withMessages([
                    'loan' => 'Peminjaman ini sudah dikembalikan.',
                ]);
            }

            $now = now();
            $denda = Denda::hitung($locked->due_at, $now);

            foreach ($locked->details as $detail) {
                $this->bookService->restoreStock($detail->book_id, $detail->quantity);
            }

            $locked->update([
                'returned_at' => $now,
                'status' => Loan::STATUS_RETURNED,
                'fine_amount' => $denda->getJumlah(),
            ]);

            $stillBorrowing = Loan::query()
                ->where('member_id', $locked->member_id)
                ->whereNull('returned_at')
                ->exists();

            if (! $stillBorrowing) {
                $this->memberService->markAsActive($locked->member_id);
            }

            return $locked->load('details');
        });
    }
}
