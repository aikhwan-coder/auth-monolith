<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Buku') }}
            </h2>
            <a href="{{ route('books.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Tambah Buku
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">No</th>
                                <th class="py-3 px-6 text-left">Judul</th>
                                <th class="py-3 px-6 text-left">ISBN</th>
                                <th class="py-3 px-6 text-left">Penulis</th>
                                <th class="py-3 px-6 text-right">Harga</th>
                                <th class="py-3 px-6 text-center">Stok</th>
                                <th class="py-3 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            @forelse ($books as $index => $book)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-6 text-left">{{ $books->firstItem() + $index }}</td>
                                    <td class="py-3 px-6 text-left">{{ $book->title }}</td>
                                    <td class="py-3 px-6 text-left">{{ $book->isbn }}</td>
                                    <td class="py-3 px-6 text-left">{{ $book->author->name ?? '-' }}</td>
                                    <td class="py-3 px-6 text-right">Rp {{ number_format($book->price, 2, ',', '.') }}</td>
                                    <td class="py-3 px-6 text-center">
                                        @if($book->stock > 0)
                                            <span class="text-green-600 font-semibold">{{ $book->stock }}</span>
                                        @else
                                            <span class="text-red-600 font-semibold">Habis</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="flex item-center justify-center space-x-2">
                                            <a href="{{ route('books.edit', $book->id) }}" class="text-blue-500 hover:text-blue-700 font-semibold">Edit</a>
                                            @if($book->stock > 0)
                                                <form action="{{ route('books.borrow', $book->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-500 hover:text-green-700 font-semibold">Pinjam</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 px-6 text-center text-gray-500">
                                        Belum ada data buku. Silakan tambah buku terlebih dahulu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $books->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
