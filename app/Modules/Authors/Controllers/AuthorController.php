<?php

namespace Modules\Authors\Controllers;

use App\Http\Controllers\Controller;
use Modules\Authors\Models\Author;
use Modules\Authors\Requests\StoreAuthorRequest;
use Modules\Authors\Requests\UpdateAuthorRequest;
use Modules\Authors\Exceptions\AuthorHasBooksException;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::withCount('books')->paginate(10);
        return view('authors.index', compact('authors'));
    }

    public function create()
    {
        return view('authors.create');
    }

    public function store(StoreAuthorRequest $request)
    {
        Author::create($request->validated());

        return redirect()
            ->route('authors.index')
            ->with('success', 'Penulis berhasil ditambahkan.');
    }

    public function show(Author $author)
    {
        $author->load('books');
        return view('authors.show', compact('author'));
    }

    public function edit(Author $author)
    {
        return view('authors.edit', compact('author'));
    }

    public function update(UpdateAuthorRequest $request, Author $author)
    {
        $author->update($request->validated());

        return redirect()
            ->route('authors.index')
            ->with('success', 'Penulis berhasil diperbarui.');
    }

    public function destroy(Author $author)
    {
        if ($author->books()->exists()) {
            throw new AuthorHasBooksException(
                $author->name,
                $author->books()->count()
            );
        }

        $author->delete();

        return redirect()
            ->route('authors.index')
            ->with('success', 'Penulis berhasil dihapus.');
    }
}
