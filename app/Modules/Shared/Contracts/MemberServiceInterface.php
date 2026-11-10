<?php

namespace Modules\Shared\Contracts;

/**
 * Contract Membership Context -> dipakai Circulation Context.
 */
interface MemberServiceInterface
{
    /** @return array{id:int,name:string,status:string,can_borrow:bool}|null */
    public function getMemberForLoan(int $memberId): ?array;

    public function markAsBorrowing(int $memberId): void;

    public function markAsActive(int $memberId): void;

    /** @return list<array{id:int,name:string,member_number:string}> */
    public function borrowableMembers(): array;
}
