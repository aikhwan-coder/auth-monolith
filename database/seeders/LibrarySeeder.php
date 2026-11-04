<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authors\Models\Author;
use Modules\Books\Models\Book;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        // Create Authors
        $authors = [
            [
                'name' => 'Pramoedya Ananta Toer',
                'email' => 'pramoedya@example.com',
                'bio' => 'Penulis Indonesia yang dikenal dengan karya Tetralogi Buru.',
            ],
            [
                'name' => 'Andrea Hirata',
                'email' => 'andrea@example.com',
                'bio' => 'Penulis novel Laskar Pelangi.',
            ],
            [
                'name' => 'Tere Liye',
                'email' => 'tere@example.com',
                'bio' => 'Penulis produktif Indonesia dengan berbagai genre.',
            ],
        ];

        foreach ($authors as $authorData) {
            $author = Author::create($authorData);

            // Create books for each author
            if ($author->name === 'Pramoedya Ananta Toer') {
                Book::create([
                    'title' => 'Bumi Manusia',
                    'isbn' => '978-979-22-2080-5',
                    'publication_year' => 1980,
                    'price' => 85000,
                    'stock' => 15,
                    'author_id' => $author->id,
                ]);
                Book::create([
                    'title' => 'Anak Semua Bangsa',
                    'isbn' => '978-979-22-2081-2',
                    'publication_year' => 1980,
                    'price' => 80000,
                    'stock' => 10,
                    'author_id' => $author->id,
                ]);
            } elseif ($author->name === 'Andrea Hirata') {
                Book::create([
                    'title' => 'Laskar Pelangi',
                    'isbn' => '978-979-22-3895-4',
                    'publication_year' => 2005,
                    'price' => 75000,
                    'stock' => 0, // Out of stock for testing
                    'author_id' => $author->id,
                ]);
                Book::create([
                    'title' => 'Sang Pemimpi',
                    'isbn' => '978-979-22-4123-7',
                    'publication_year' => 2006,
                    'price' => 70000,
                    'stock' => 8,
                    'author_id' => $author->id,
                ]);
            } elseif ($author->name === 'Tere Liye') {
                Book::create([
                    'title' => 'Rindu',
                    'isbn' => '978-602-03-0955-8',
                    'publication_year' => 2014,
                    'price' => 68000,
                    'stock' => 20,
                    'author_id' => $author->id,
                ]);
                Book::create([
                    'title' => 'Hujan',
                    'isbn' => '978-602-06-1767-2',
                    'publication_year' => 2016,
                    'price' => 72000,
                    'stock' => 12,
                    'author_id' => $author->id,
                ]);
            }
        }
    }
}
