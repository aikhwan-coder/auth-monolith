<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Author;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\BookCategory;
use Modules\Catalog\Models\Publisher;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\MembershipRole;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        if (Book::withTrashed()->exists()) {
            $this->command?->warn('Data perpustakaan sudah ada, seeder dilewati.');

            return;
        }

        // ---------- Membership ----------
        $roles = collect([
            ['name' => 'Mahasiswa', 'slug' => 'mahasiswa', 'max_loans' => 3],
            ['name' => 'Dosen', 'slug' => 'dosen', 'max_loans' => 5],
            ['name' => 'Umum', 'slug' => 'umum', 'max_loans' => 2],
        ])->map(fn (array $r) => MembershipRole::create($r));

        Member::factory()->count(10)->create()->each(function (Member $member) use ($roles) {
            $member->roles()->attach($roles->random()->id, ['assigned_at' => now()]);
        });

        // ---------- Catalog ----------
        $authors = collect([
            'Andrea Hirata', 'Tere Liye', 'Pramoedya Ananta Toer', 'Dee Lestari',
            'Robert C. Martin', 'Martin Fowler', 'Eric Evans', 'Abraham Silberschatz',
        ])->map(fn (string $name) => Author::create(['name' => $name]));

        collect([
            ['Gramedia Pustaka Utama', 'Jakarta'],
            ['Bentang Pustaka', 'Yogyakarta'],
            ['Informatika Bandung', 'Bandung'],
            ['Addison-Wesley', 'Boston'],
        ])->each(fn (array $p) => Publisher::create(['name' => $p[0], 'city' => $p[1]]));

        // Kategori hierarkis (self-referencing): induk -> sub-kategori
        $tree = [
            'Fiksi' => ['Novel', 'Cerita Pendek'],
            'Non-Fiksi' => ['Biografi'],
            'Teknologi' => ['Pemrograman', 'Basis Data'],
            'Sains' => ['Fisika'],
        ];
        $categories = collect();
        foreach ($tree as $parentName => $children) {
            $parent = BookCategory::create([
                'name' => $parentName,
                'slug' => str($parentName)->slug()->toString(),
            ]);
            $categories->push($parent);

            foreach ($children as $childName) {
                $categories->push(BookCategory::create([
                    'name' => $childName,
                    'slug' => str($childName)->slug()->toString(),
                    'parent_id' => $parent->id,
                ]));
            }
        }

        $books = Book::factory()->count(30)->create();
        Book::factory()->outOfStock()->count(2)->create();

        Book::all()->each(function (Book $book) use ($categories) {
            $book->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->mapWithKeys(
                    fn ($id) => [$id => ['added_at' => now()]]
                )->all()
            );
        });

        // ---------- Circulation: contoh peminjaman lewat LoanService (valid & lengkap) ----------
        $service = app(LoanService::class);
        $members = Member::all();
        $available = Book::available()->get();

        for ($i = 0; $i < 8; $i++) {
            try {
                $picked = $available->random(rand(1, 2));
                $loan = $service->borrow(
                    $members->random()->id,
                    $picked->map(fn (Book $b) => ['book_id' => $b->id, 'quantity' => 1])->all(),
                );

                // Dua peminjaman pertama dikembalikan; yang pertama sengaja terlambat 5 hari (ada denda)
                if ($i === 0) {
                    $loan->update(['due_at' => now()->subDays(5)]);
                }
                if ($i <= 1) {
                    $service->returnLoan($loan);
                }
            } catch (\Throwable $e) {
                $this->command?->warn('Lewati 1 peminjaman contoh: '.$e->getMessage());
            }
        }

        $this->command?->info(
            'Seeder perpustakaan selesai: '.Book::count().' buku, '.Member::count().' anggota, '.Loan::count().' peminjaman.'
        );
    }
}
