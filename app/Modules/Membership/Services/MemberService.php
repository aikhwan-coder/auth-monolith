<?php

namespace Modules\Membership\Services;

use Modules\Membership\Models\Member;
use Modules\Shared\Contracts\MemberServiceInterface;

class MemberService implements MemberServiceInterface
{
    public function getMemberForLoan(int $memberId): ?array
    {
        $member = Member::find($memberId);

        if (! $member) {
            return null;
        }

        return [
            'id' => $member->id,
            'name' => $member->name,
            'status' => $member->status,
            'can_borrow' => $member->canBorrow(),
        ];
    }

    public function markAsBorrowing(int $memberId): void
    {
        Member::query()
            ->whereKey($memberId)
            ->where('status', Member::STATUS_ACTIVE)
            ->update(['status' => Member::STATUS_BORROWING]);
    }

    public function markAsActive(int $memberId): void
    {
        Member::query()
            ->whereKey($memberId)
            ->where('status', Member::STATUS_BORROWING)
            ->update(['status' => Member::STATUS_ACTIVE]);
    }

    public function borrowableMembers(): array
    {
        return Member::borrowable()
            ->orderBy('name')
            ->get(['id', 'name', 'member_number'])
            ->map(fn (Member $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'member_number' => $m->member_number,
            ])
            ->all();
    }
}
