<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Peminjaman {{ $loan->loan_number }}</h2>
            <a href="{{ route('library.loans.index') }}" class="text-indigo-700 text-sm">&larr; Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900 space-y-4">
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Anggota</dt><dd class="font-semibold">{{ $loan->member_name }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt>
                        <dd class="font-semibold">
                            @if ($loan->isReturned()) Dikembalikan
                            @elseif ($loan->isOverdue()) Terlambat
                            @else Dipinjam @endif
                        </dd>
                    </div>
                    <div><dt class="text-gray-500">Tanggal pinjam</dt><dd>{{ $loan->borrowed_at->format('d M Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Jatuh tempo</dt><dd>{{ $loan->due_at->format('d M Y') }}</dd></div>
                    <div><dt class="text-gray-500">Tanggal kembali</dt><dd>{{ $loan->returned_at?->format('d M Y H:i') ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Denda</dt><dd>Rp {{ number_format((float) $loan->fine_amount, 0, ',', '.') }}</dd></div>
                </dl>

                <table class="min-w-full border border-gray-200 text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase">
                            <th class="py-2 px-4 text-left">Judul (snapshot)</th>
                            <th class="py-2 px-4 text-left">ISBN</th>
                            <th class="py-2 px-4 text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loan->details as $detail)
                            <tr class="border-t border-gray-200">
                                <td class="py-2 px-4">{{ $detail->book_title }}</td>
                                <td class="py-2 px-4">{{ $detail->isbn }}</td>
                                <td class="py-2 px-4 text-center">{{ $detail->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @unless ($loan->isReturned())
                    <form method="POST" action="{{ route('library.loans.return', $loan->id) }}" onsubmit="return confirm('Tandai buku sudah dikembalikan?');">
                        @csrf @method('PATCH')
                        <button class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded">Kembalikan Buku</button>
                    </form>
                @endunless
            </div>
        </div>
    </div>
</x-app-layout>
