<?php

namespace Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\BookCategory;
use Modules\Shared\Concerns\ChecksAdmin;

class BookController extends Controller
{
    use ChecksAdmin;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'trashed' => ['nullable', 'boolean'],
        ]);

        $isAdmin = $this->isAdmin($request->user());

        // Eager loading: author, publisher, categories => 4 query total (bukan N+1)
        $query = Book::query()
            ->with(['author:id,name', 'publisher:id,name', 'categories:id,name'])
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->search($s))
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->inCategory((int) $c))
            ->orderBy('title');

        if ($isAdmin && ! empty($filters['trashed'])) {
            $query->onlyTrashed();
        }

        return view('library.books.index', [
            'books' => $query->paginate(10)->withQueryString(),
            'categories' => BookCategory::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete(); // soft delete

        return back()->with('success', "Buku '{$book->title}' dihapus (soft delete).");
    }

    public function restore(int $id): RedirectResponse
    {
        $book = Book::onlyTrashed()->findOrFail($id);
        $book->restore();

        return back()->with('success', "Buku '{$book->title}' berhasil dipulihkan.");
    }
}
