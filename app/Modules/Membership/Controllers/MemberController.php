<?php

namespace Modules\Membership\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Membership\Models\Member;
use Modules\Shared\Concerns\ChecksAdmin;

class MemberController extends Controller
{
    use ChecksAdmin;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'trashed' => ['nullable', 'boolean'],
        ]);

        $isAdmin = $this->isAdmin($request->user());

        // Eager loading roles => 2 query total (bukan N+1)
        $query = Member::query()
            ->with('roles:id,name')
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->search($s))
            ->orderBy('name');

        if ($isAdmin && ! empty($filters['trashed'])) {
            $query->onlyTrashed();
        }

        return view('library.members.index', [
            'members' => $query->paginate(10)->withQueryString(),
            'filters' => $filters,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete(); // soft delete

        return back()->with('success', "Anggota '{$member->name}' dihapus (soft delete).");
    }

    public function restore(int $id): RedirectResponse
    {
        $member = Member::onlyTrashed()->findOrFail($id);
        $member->restore();

        return back()->with('success', "Anggota '{$member->name}' berhasil dipulihkan.");
    }
}
