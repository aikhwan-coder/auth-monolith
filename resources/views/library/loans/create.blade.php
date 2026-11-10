<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Peminjaman Baru</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('library.loans.store') }}" class="p-6 space-y-5 text-gray-900">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Anggota</label>
                        <select name="member_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="">-- pilih anggota --</option>
                            @foreach ($members as $member)
                                <option value="{{ $member['id'] }}" @selected(old('member_id') == $member['id'])>{{ $member['member_number'] }} - {{ $member['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lama pinjam (hari)</label>
                        <input type="number" name="days" min="1" max="30" value="{{ old('days', 7) }}" class="mt-1 block w-32 border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buku (isi minimal 1 baris)</label>
                        @for ($i = 0; $i < 3; $i++)
                            <div class="flex gap-3 mb-2">
                                <select name="items[{{ $i }}][book_id]" class="flex-1 border-gray-300 rounded-md">
                                    <option value="">-- pilih buku --</option>
                                    @foreach ($books as $book)
                                        <option value="{{ $book['id'] }}" @selected(old("items.$i.book_id") == $book['id'])>{{ $book['title'] }} (stok {{ $book['stock'] }})</option>
                                    @endforeach
                                </select>
                                <input type="number" name="items[{{ $i }}][quantity]" min="1" max="5" value="{{ old("items.$i.quantity", 1) }}" class="w-24 border-gray-300 rounded-md">
                            </div>
                        @endfor
                    </div>

                    <div class="flex items-center gap-3">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan Peminjaman</button>
                        <a href="{{ route('library.loans.index') }}" class="text-gray-600">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
