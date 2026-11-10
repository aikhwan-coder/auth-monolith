<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Katalog Buku</h2>
            <div class="space-x-2 text-sm">
                <a href="{{ route('library.books.index') }}" class="text-indigo-700 font-semibold">Buku</a>
                <a href="{{ route('library.members.index') }}" class="text-indigo-700">Anggota</a>
                <a href="{{ route('library.loans.index') }}" class="text-indigo-700">Peminjaman</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
                        <div>
                            <label class="block text-xs text-gray-500">Cari (judul / ISBN / penulis)</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="border-gray-300 rounded-md text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Kategori</label>
                            <select name="category" class="border-gray-300 rounded-md text-sm">
                                <option value="">Semua</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(($filters['category'] ?? null) == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($isAdmin)
                            <label class="text-sm flex items-center gap-1">
                                <input type="checkbox" name="trashed" value="1" @checked(! empty($filters['trashed']))> Tampilkan yang dihapus
                            </label>
                        @endif
                        <button class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded">Filter</button>
                    </form>

                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-4 text-left">Judul</th>
                                <th class="py-3 px-4 text-left">ISBN</th>
                                <th class="py-3 px-4 text-left">Penulis</th>
                                <th class="py-3 px-4 text-left">Penerbit</th>
                                <th class="py-3 px-4 text-left">Kategori</th>
                                <th class="py-3 px-4 text-center">Stok</th>
                                @if ($isAdmin)<th class="py-3 px-4 text-center">Aksi</th>@endif
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            @forelse ($books as $book)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-4">{{ $book->title }}</td>
                                    <td class="py-3 px-4">{{ $book->isbn }}</td>
                                    <td class="py-3 px-4">{{ $book->author->name }}</td>
                                    <td class="py-3 px-4">{{ $book->publisher->name }}</td>
                                    <td class="py-3 px-4">{{ $book->categories->pluck('name')->join(', ') }}</td>
                                    <td class="py-3 px-4 text-center">{{ $book->stock }}</td>
                                    @if ($isAdmin)
                                        <td class="py-3 px-4 text-center">
                                            @if ($book->trashed())
                                                <form action="{{ route('library.books.restore', $book->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button class="text-emerald-600 hover:text-emerald-800 font-semibold">Pulihkan</button>
                                                </form>
                                            @else
                                                <form action="{{ route('library.books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Hapus buku ini (soft delete)?');">
                                                    @csrf @method('DELETE')
                                                    <button class="text-red-500 hover:text-red-700 font-semibold">Hapus</button>
                                                </form>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-4 px-6 text-center text-gray-500">Tidak ada buku.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $books->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
