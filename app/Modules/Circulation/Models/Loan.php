<?php

namespace Modules\Circulation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;

    public const STATUS_BORROWED = 'borrowed';

    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'member_id',
        'member_name',
        'loan_number',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
        'fine_amount',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_at' => 'datetime',
        'returned_at' => 'datetime',
        'fine_amount' => 'decimal:2',
    ];

    // HasMany: Peminjaman -> DetailPeminjaman
    public function details(): HasMany
    {
        return $this->hasMany(LoanDetail::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('returned_at');
    }

    public function scopeReturned(Builder $query): Builder
    {
        return $query->whereNotNull('returned_at');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNull('returned_at')->where('due_at', '<', now());
    }

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }

    public function isOverdue(): bool
    {
        return ! $this->isReturned() && $this->due_at->isPast();
    }
}
