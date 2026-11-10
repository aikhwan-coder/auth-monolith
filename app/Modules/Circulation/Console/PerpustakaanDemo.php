<?php

namespace Modules\Circulation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;
use Modules\Shared\Exceptions\InsufficientStockException;
use Throwable;

/**
 * Skrip pengujian untuk BAB IV laporan (bahan screenshot).
 * Catatan: ini harness uji, jadi sengaja menyentuh model dari beberapa context.
 */
class PerpustakaanDemo extends Command
{
    protected $signature = 'perpustakaan:demo {scenario=all : query|transaction|softdelete|event|data|all}';

    protected $description = 'Demo pengujian: query eager loading, transaction, soft delete, domain event, data DB';

    public function handle(): int
    {
        $scenarios = [
            'transaction' => 'testTransaction',
            'query' => 'testQuery',
            'softdelete' => 'testSoftDelete',
            'event' => 'testEvent',
            'data' => 'showData',
        ];

        $name = $this->argument('scenario');

        if ($name === 'all') {
            foreach ($scenarios as $method) {
                $this->{$method}();
                $this->newLine();
            }

            return self::SUCCESS;
        }

        if (! isset($scenarios[$name])) {
            $this->error("Skenario tidak dikenal: {$name}. Pilih: ".implode(', ', array_keys($scenarios)).', all');

            return self::FAILURE;
        }

        $this->{$scenarios[$name]}();

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------
    // 1. Perbandingan jumlah query: tanpa vs dengan eager loading
    // ------------------------------------------------------------------
    private function testQuery(): void
    {
        $this->components->info('PENGUJIAN 1: Perbandingan jumlah query (N+1 vs eager loading)');
        DB::enableQueryLog();

        // --- Buku (20 data): author, publisher, categories
        DB::flushQueryLog();
        foreach (Book::query()->limit(20)->get() as $book) {
            $book->author?->name;
            $book->publisher?->name;
            $book->categories->count();
        }
        $booksLazy = count(DB::getQueryLog());

        DB::flushQueryLog();
        foreach (Book::query()->with(['author', 'publisher', 'categories'])->limit(20)->get() as $book) {
            $book->author?->name;
            $book->publisher?->name;
            $book->categories->count();
        }
        $booksEager = count(DB::getQueryLog());

        // --- Peminjaman (20 data): details
        DB::flushQueryLog();
        foreach (Loan::query()->limit(20)->get() as $loan) {
            $loan->details->count();
        }
        $loansLazy = count(DB::getQueryLog());

        DB::flushQueryLog();
        foreach (Loan::query()->with('details')->limit(20)->get() as $loan) {
            $loan->details->count();
        }
        $loansEager = count(DB::getQueryLog());

        $this->table(
            ['Skenario', 'SEBELUM (tanpa eager loading)', 'SESUDAH (with())', 'Selisih'],
            [
                ['Daftar buku + author + publisher + kategori', $booksLazy, $booksEager, $booksLazy - $booksEager],
                ['Daftar peminjaman + detail', $loansLazy, $loansEager, $loansLazy - $loansEager],
            ],
        );

        DB::disableQueryLog();
    }

    // ------------------------------------------------------------------
    // 2. Transaction: berhasil dan rollback
    // ------------------------------------------------------------------
    private function testTransaction(): void
    {
        $this->components->info('PENGUJIAN 2: Database transaction (berhasil & rollback)');

        $member = Member::borrowable()->first();
        $books = Book::available()->where('stock', '>=', 3)->limit(2)->get();

        if (! $member || $books->count() < 2) {
            $this->error('Data belum cukup. Jalankan dulu: php artisan db:seed --class=LibrarySeeder');

            return;
        }

        [$a, $b] = [$books[0], $books[1]];
        $service = app(LoanService::class);

        // ---- A. Berhasil
        $this->line('<fg=green>A. Peminjaman BERHASIL (2 buku @1 eksemplar)</>');
        $stockA = Book::find($a->id)->stock;
        $stockB = Book::find($b->id)->stock;
        $loansBefore = Loan::count();

        $loan = $service->borrow($member->id, [
            ['book_id' => $a->id, 'quantity' => 1],
            ['book_id' => $b->id, 'quantity' => 1],
        ]);

        $this->table(['Item', 'Sebelum', 'Sesudah'], [
            ["Stok: {$a->title}", $stockA, Book::find($a->id)->stock],
            ["Stok: {$b->title}", $stockB, Book::find($b->id)->stock],
            ['Jumlah record loans', $loansBefore, Loan::count()],
        ]);
        $this->line("Loan dibuat: {$loan->loan_number} (anggota: {$loan->member_name})");

        // ---- B. Gagal -> rollback
        $this->newLine();
        $this->line("<fg=red>B. Peminjaman GAGAL -> ROLLBACK (buku 1 diminta 1, buku 2 diminta 9999)</>");
        $stockA = Book::find($a->id)->stock;
        $stockB = Book::find($b->id)->stock;
        $loansBefore = Loan::count();
        $detailsBefore = DB::table('loan_details')->count();

        try {
            $service->borrow($member->id, [
                ['book_id' => $a->id, 'quantity' => 1],
                ['book_id' => $b->id, 'quantity' => 9999],
            ]);
            $this->error('Seharusnya gagal, tetapi berhasil!');
        } catch (InsufficientStockException $e) {
            $this->line('Exception tertangkap: '.$e->getMessage());
        } catch (Throwable $e) {
            $this->line(get_class($e).': '.$e->getMessage());
        }

        $this->table(['Item', 'Sebelum', 'Sesudah (harus SAMA)'], [
            ["Stok: {$a->title}", $stockA, Book::find($a->id)->stock],
            ["Stok: {$b->title}", $stockB, Book::find($b->id)->stock],
            ['Jumlah record loans', $loansBefore, Loan::count()],
            ['Jumlah record loan_details', $detailsBefore, DB::table('loan_details')->count()],
        ]);
    }

    // ------------------------------------------------------------------
    // 3. Soft delete + restore
    // ------------------------------------------------------------------
    private function testSoftDelete(): void
    {
        $this->components->info('PENGUJIAN 3: Soft delete & restore (Book)');

        $book = Book::query()->orderBy('id')->first();

        if (! $book) {
            $this->error('Belum ada buku. Jalankan seeder dulu.');

            return;
        }

        $rows = [['Awal', Book::count(), Book::withTrashed()->count(), Book::onlyTrashed()->count()]];

        $book->delete();
        $rows[] = ["Setelah delete() '{$book->title}'", Book::count(), Book::withTrashed()->count(), Book::onlyTrashed()->count()];

        $book->restore();
        $rows[] = ['Setelah restore()', Book::count(), Book::withTrashed()->count(), Book::onlyTrashed()->count()];

        $this->table(['Tahap', 'Book::count()', 'withTrashed()->count()', 'onlyTrashed()->count()'], $rows);
    }

    // ------------------------------------------------------------------
    // 4. Domain event BookBorrowed -> listener menulis log
    // ------------------------------------------------------------------
    private function testEvent(): void
    {
        $this->components->info('PENGUJIAN 4: Domain event BookBorrowed -> LogBookBorrowed');

        $member = Member::borrowable()->first();
        $book = Book::available()->first();

        if (! $member || ! $book) {
            $this->error('Data belum cukup. Jalankan seeder dulu.');

            return;
        }

        $loan = app(LoanService::class)->borrow($member->id, [['book_id' => $book->id, 'quantity' => 1]]);
        $this->line("Peminjaman {$loan->loan_number} dibuat -> event BookBorrowed ditembakkan.");

        $files = glob(storage_path('logs/laravel*.log')) ?: [];
        usort($files, fn ($x, $y) => filemtime($y) <=> filemtime($x));

        if ($files === []) {
            $this->warn('File log tidak ditemukan di storage/logs.');

            return;
        }

        $handle = fopen($files[0], 'rb');
        fseek($handle, max(0, filesize($files[0]) - 30000));
        $chunk = (string) stream_get_contents($handle);
        fclose($handle);

        $lines = array_values(array_filter(
            explode("\n", $chunk),
            fn ($line) => str_contains($line, 'Buku dipinjam'),
        ));

        $this->line('Isi log ('.basename($files[0]).'), 2 entri terakhir:');
        foreach (array_slice($lines, -2) as $line) {
            $this->line('  '.$line);
        }
    }

    // ------------------------------------------------------------------
    // 5. Ringkasan data di database
    // ------------------------------------------------------------------
    private function showData(): void
    {
        $this->components->info('DATA DI DATABASE');

        $this->table(['Tabel', 'Jumlah baris'], [
            ['authors', DB::table('authors')->count()],
            ['publishers', DB::table('publishers')->count()],
            ['book_categories', DB::table('book_categories')->count()],
            ['books (total)', DB::table('books')->count()],
            ['books (soft deleted)', DB::table('books')->whereNotNull('deleted_at')->count()],
            ['book_category (pivot)', DB::table('book_category')->count()],
            ['members', DB::table('members')->count()],
            ['loans', DB::table('loans')->count()],
            ['loan_details', DB::table('loan_details')->count()],
        ]);

        $this->line('Tabel loans (5 terbaru):');
        $this->table(
            ['id', 'loan_number', 'member_name', 'status', 'due_at', 'returned_at', 'fine_amount', 'deleted_at'],
            DB::table('loans')->orderByDesc('id')->limit(5)
                ->get(['id', 'loan_number', 'member_name', 'status', 'due_at', 'returned_at', 'fine_amount', 'deleted_at'])
                ->map(fn ($r) => (array) $r)->all(),
        );

        $this->line('Tabel loan_details (6 terbaru, snapshot judul & ISBN):');
        $this->table(
            ['id', 'loan_id', 'book_id', 'book_title', 'isbn', 'quantity'],
            DB::table('loan_details')->orderByDesc('id')->limit(6)
                ->get(['id', 'loan_id', 'book_id', 'book_title', 'isbn', 'quantity'])
                ->map(fn ($r) => (array) $r)->all(),
        );

        $this->line('Tabel book_category (pivot, kolom added_at):');
        $this->table(
            ['book_id', 'category_id', 'added_at'],
            DB::table('book_category')->limit(5)->get(['book_id', 'category_id', 'added_at'])
                ->map(fn ($r) => (array) $r)->all(),
        );
    }
}
