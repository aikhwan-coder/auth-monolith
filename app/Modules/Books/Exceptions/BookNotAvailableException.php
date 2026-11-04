<?php

namespace Modules\Books\Exceptions;

use Exception;

class BookNotAvailableException extends Exception
{
    protected $bookTitle;
    protected $availableStock;

    public function __construct(string $bookTitle, int $availableStock)
    {
        $this->bookTitle = $bookTitle;
        $this->availableStock = $availableStock;

        parent::__construct(
            "Buku '{$bookTitle}' tidak tersedia. Stok tersedia: {$availableStock}."
        );
    }

    public function getBookTitle(): string
    {
        return $this->bookTitle;
    }

    public function getAvailableStock(): int
    {
        return $this->availableStock;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'book_not_available',
                'details' => [
                    'book' => $this->bookTitle,
                    'available' => $this->availableStock,
                ],
            ], 422);
        }

        return back()->withErrors([
            'stock' => $this->getMessage(),
        ]);
    }
}
