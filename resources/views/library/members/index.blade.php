<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Anggota Perpustakaan</h2>
            <div class="space-x-2 text-sm">
                <a href="{{ route('library.books.index') }}" class="text-indigo-700">Buku</a>
                <a href="{{ route('library.members.index') }}" class="text-indigo-700 font-semibold">Anggota</a>
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
                            <label class="block text-xs text-gray-500">Cari (nama / no. anggota / email)</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="border-gray-300 rounded-md text-sm">
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
                                <th class="py-3 px-4 text-left">No. Anggota</th>
                                <th class="py-3 px-4 text-left">Nama</th>
                                <th class="py-3 px-4 text-left">Email</th>
                                <th class="py-3 px-4 text-left">Role</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-left">Berlaku s/d</th>
                                @if ($isAdmin)<th class="py-3 px-4 text-center">Aksi</th>@endif
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            @forelse ($members as $member)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-4">{{ $member->member_number }}</td>
                                    <td class="py-3 px-4">{{ $member->name }}</td>
                                    <td class="py-3 px-4">{{ $member->email }}</td>
                                    <td class="py-3 px-4">{{ $member->roles->pluck('name')->join(', ') }}</td>
                                    <td class="py-3 px-4 text-center">{{ $member->status }}</td>
                                    <td class="py-3 px-4">{{ $member->expires_at?->format('d M Y') ?? '-' }}</td>
                                    @if ($isAdmin)
                                        <td class="py-3 px-4 text-center">
                                            @if ($member->trashed())
                                                <form action="{{ route('library.members.restore', $member->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button class="text-emerald-600 hover:text-emerald-800 font-semibold">Pulihkan</button>
                                                </form>
                                            @else
                                                <form action="{{ route('library.members.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Hapus anggota ini (soft delete)?');">
                                                    @csrf @method('DELETE')
                                                    <button class="text-red-500 hover:text-red-700 font-semibold">Hapus</button>
                                                </form>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-4 px-6 text-center text-gray-500">Tidak ada anggota.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $members->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
