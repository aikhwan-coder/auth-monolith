<?php

namespace Modules\Authors\Exceptions;

use Exception;

class AuthorHasBooksException extends Exception
{
    protected $authorName;
    protected $bookCount;

    public function __construct(string $authorName, int $bookCount)
    {
        $this->authorName = $authorName;
        $this->bookCount = $bookCount;

        parent::__construct(
            "Penulis '{$authorName}' masih memiliki {$bookCount} buku. Hapus buku terlebih dahulu."
        );
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function getBookCount(): int
    {
        return $this->bookCount;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'author_has_books',
                'details' => [
                    'author' => $this->authorName,
                    'book_count' => $this->bookCount,
                ],
            ], 409);
        }

        return back()->withErrors([
            'author' => $this->getMessage(),
        ]);
    }
}
