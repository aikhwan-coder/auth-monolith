<?php

namespace Modules\Books\Controllers;

use App\Http\Controllers\Controller;
use Modules\Books\Models\Book;
use Modules\Authors\Models\Author;
use Modules\Books\Requests\StoreBookRequest;
use Modules\Books\Requests\UpdateBookRequest;
use Modules\Books\Exceptions\BookNotAvailableException;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('author')->paginate(10);
        return view('books.index', compact('books'));
    }

    public function create()
    {
        $authors = Author::all();
        return view('books.create', compact('authors'));
    }

    public function store(StoreBookRequest $request)
    {
        Book::create($request->validated());

        return redirect()
            ->route('books.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show(Book $book)
    {
        $book->load('author');
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $authors = Author::all();
        return view('books.edit', compact('book', 'authors'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $book->update($request->validated());

        return redirect()
            ->route('books.index')
            ->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', 'Buku berhasil dihapus.');
    }

    public function borrow(Book $book)
    {
        if ($book->stock <= 0) {
            throw new BookNotAvailableException($book->title, $book->stock);
        }

        $book->decrement('stock');

        return redirect()
            ->route('books.index')
            ->with('success', "Buku '{$book->title}' berhasil dipinjam. Stok tersisa: {$book->stock}.");
    }
}
