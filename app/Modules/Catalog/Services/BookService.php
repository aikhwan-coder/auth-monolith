<?php

namespace Modules\Catalog\Services;

use Modules\Catalog\Models\Book;
use Modules\Shared\Contracts\BookServiceInterface;

class BookService implements BookServiceInterface
{
    public function getBookForLoan(int $bookId): ?array
    {
        $book = Book::find($bookId);

        if (! $book) {
            return null;
        }

        return [
            'id' => $book->id,
            'title' => $book->title,
            'isbn' => $book->isbn,
            'stock' => (int) $book->stock,
            'is_active' => (bool) $book->is_active,
        ];
    }

    public function reduceStock(int $bookId, int $quantity): bool
    {
        $book = Book::lockForUpdate()->find($bookId);

        if (! $book || ! $book->isInStock($quantity)) {
            return false;
        }

        $book->decrement('stock', $quantity);

        return true;
    }

    public function restoreStock(int $bookId, int $quantity): bool
    {
        // withTrashed: buku yang sudah di-soft delete tetap boleh dikembalikan stoknya
        $book = Book::withTrashed()->lockForUpdate()->find($bookId);

        if (! $book) {
            return false;
        }

        $book->increment('stock', $quantity);

        return true;
    }

    public function availableBooks(): array
    {
        return Book::available()
            ->orderBy('title')
            ->get(['id', 'title', 'isbn', 'stock'])
            ->map(fn (Book $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'isbn' => $b->isbn,
                'stock' => (int) $b->stock,
            ])
            ->all();
    }
}
