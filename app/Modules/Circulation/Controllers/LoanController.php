<?php

namespace Modules\Circulation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Requests\StoreLoanRequest;
use Modules\Circulation\Services\LoanService;
use Modules\Shared\Concerns\ChecksAdmin;
use Modules\Shared\Contracts\BookServiceInterface;
use Modules\Shared\Contracts\MemberServiceInterface;

class LoanController extends Controller
{
    use ChecksAdmin;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['borrowed', 'overdue', 'returned'])],
            'trashed' => ['nullable', 'boolean'],
        ]);

        $isAdmin = $this->isAdmin($request->user());

        // Eager loading details => 2 query total (bukan N+1)
        $query = Loan::query()
            ->with('details')
            ->when($filters['status'] ?? null, function ($q, string $status) {
                return match ($status) {
                    'borrowed' => $q->active(),
                    'overdue' => $q->overdue(),
                    'returned' => $q->returned(),
                };
            })
            ->orderByDesc('borrowed_at')
            ->orderByDesc('id');

        if ($isAdmin && ! empty($filters['trashed'])) {
            $query->onlyTrashed();
        }

        return view('library.loans.index', [
            'loans' => $query->paginate(10)->withQueryString(),
            'filters' => $filters,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function create(BookServiceInterface $books, MemberServiceInterface $members): View
    {
        // Data lintas context diambil lewat CONTRACT, bukan model context lain
        return view('library.loans.create', [
            'books' => $books->availableBooks(),
            'members' => $members->borrowableMembers(),
        ]);
    }

    public function store(StoreLoanRequest $request, LoanService $service): RedirectResponse
    {
        $loan = $service->borrow(
            (int) $request->validated('member_id'),
            $request->validated('items'),
            (int) ($request->validated('days') ?? 7),
        );

        return redirect()
            ->route('library.loans.show', $loan)
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function show(Request $request, Loan $loan): View
    {
        return view('library.loans.show', [
            'loan' => $loan->load('details'),
            'isAdmin' => $this->isAdmin($request->user()),
        ]);
    }

    public function returnBook(Loan $loan, LoanService $service): RedirectResponse
    {
        $loan = $service->returnLoan($loan);

        $pesan = 'Buku berhasil dikembalikan.';
        if ((float) $loan->fine_amount > 0) {
            $pesan .= ' Denda keterlambatan: Rp '.number_format((float) $loan->fine_amount, 0, ',', '.').'.';
        }

        return redirect()->route('library.loans.show', $loan)->with('success', $pesan);
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        if (! $loan->isReturned()) {
            return back()->withErrors(['loan' => 'Peminjaman yang masih aktif tidak boleh dihapus.']);
        }

        $loan->delete(); // soft delete

        return redirect()->route('library.loans.index')
            ->with('success', "Peminjaman {$loan->loan_number} dihapus (soft delete).");
    }

    public function restore(int $id): RedirectResponse
    {
        $loan = Loan::onlyTrashed()->findOrFail($id);
        $loan->restore();

        return redirect()->route('library.loans.index')
            ->with('success', "Peminjaman {$loan->loan_number} berhasil dipulihkan.");
    }
}
