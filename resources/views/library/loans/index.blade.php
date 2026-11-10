<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Peminjaman Buku</h2>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('library.books.index') }}" class="text-indigo-700">Buku</a>
                <a href="{{ route('library.members.index') }}" class="text-indigo-700">Anggota</a>
                <a href="{{ route('library.loans.index') }}" class="text-indigo-700 font-semibold">Peminjaman</a>
                <a href="{{ route('library.loans.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">+ Peminjaman Baru</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
                        <div>
                            <label class="block text-xs text-gray-500">Status</label>
                            <select name="status" class="border-gray-300 rounded-md text-sm">
                                <option value="">Semua</option>
                                <option value="borrowed" @selected(($filters['status'] ?? '') === 'borrowed')>Sedang dipinjam</option>
                                <option value="overdue" @selected(($filters['status'] ?? '') === 'overdue')>Terlambat</option>
                                <option value="returned" @selected(($filters['status'] ?? '') === 'returned')>Dikembalikan</option>
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
                                <th class="py-3 px-4 text-left">No. Pinjam</th>
                                <th class="py-3 px-4 text-left">Anggota</th>
                                <th class="py-3 px-4 text-left">Buku</th>
                                <th class="py-3 px-4 text-left">Jatuh Tempo</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Denda</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            @forelse ($loans as $loan)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-4"><a href="{{ route('library.loans.show', $loan->id) }}" class="text-indigo-700 font-semibold">{{ $loan->loan_number }}</a></td>
                                    <td class="py-3 px-4">{{ $loan->member_name }}</td>
                                    <td class="py-3 px-4">{{ $loan->details->map(fn ($d) => $d->book_title.' x'.$d->quantity)->join(', ') }}</td>
                                    <td class="py-3 px-4">{{ $loan->due_at->format('d M Y') }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @if ($loan->isReturned()) Dikembalikan
                                        @elseif ($loan->isOverdue()) <span class="text-red-600 font-semibold">Terlambat</span>
                                        @else Dipinjam @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">Rp {{ number_format((float) $loan->fine_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @if ($loan->trashed())
                                            <form action="{{ route('library.loans.restore', $loan->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button class="text-emerald-600 hover:text-emerald-800 font-semibold">Pulihkan</button>
                                            </form>
                                        @elseif ($isAdmin && $loan->isReturned())
                                            <form action="{{ route('library.loans.destroy', $loan->id) }}" method="POST" onsubmit="return confirm('Hapus peminjaman ini (soft delete)?');">
                                                @csrf @method('DELETE')
                                                <button class="text-red-500 hover:text-red-700 font-semibold">Hapus</button>
                                            </form>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-4 px-6 text-center text-gray-500">Belum ada peminjaman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $loans->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
