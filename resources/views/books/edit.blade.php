<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Buku') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('books.update', $book->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Judul Buku</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $book->title) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                            @error('title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN</label>
                            <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                            @error('isbn')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="publication_year" class="block text-sm font-medium text-gray-700">Tahun Terbit</label>
                            <input type="number" name="publication_year" id="publication_year" value="{{ old('publication_year', $book->publication_year) }}" min="1900" max="{{ date('Y') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                            @error('publication_year')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="price" class="block text-sm font-medium text-gray-700">Harga</label>
                            <input type="number" name="price" id="price" value="{{ old('price', $book->price) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                            @error('price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="stock" class="block text-sm font-medium text-gray-700">Stok</label>
                            <input type="number" name="stock" id="stock" value="{{ old('stock', $book->stock) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                            @error('stock')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="author_id" class="block text-sm font-medium text-gray-700">Penulis</label>
                            <select name="author_id" id="author_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                                @foreach ($authors as $author)
                                    <option value="{{ $author->id }}" {{ old('author_id', $book->author_id) == $author->id ? 'selected' : '' }}>
                                        {{ $author->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('author_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Perbarui
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
