<?php

namespace Modules\Membership\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MembershipRole extends Model
{
    protected $fillable = ['name', 'slug', 'max_loans', 'description'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_role', 'membership_role_id', 'member_id')
            ->withPivot('assigned_at');
    }
}
