<?php

namespace Modules\Membership\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(MemberFactory::class)]
class Member extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_BORROWING = 'borrowing';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'member_number',
        'name',
        'email',
        'phone',
        'status',
        'joined_at',
        'expires_at',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'expires_at' => 'date',
    ];

    // Many-to-Many dengan pivot assigned_at
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(MembershipRole::class, 'member_role', 'member_id', 'membership_role_id')
            ->withPivot('assigned_at');
    }

    // Anggota yang boleh meminjam: tidak di-suspend dan belum kedaluwarsa
    public function scopeBorrowable(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_BORROWING])
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString());
            });
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('member_number', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
        });
    }

    public function canBorrow(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_BORROWING], true)
            && ($this->expires_at === null || $this->expires_at->gte(now()->startOfDay()));
    }
}
