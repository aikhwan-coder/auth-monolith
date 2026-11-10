<?php

namespace Modules\Shared\Contracts;

/**
 * Contract Catalog Context -> dipakai Circulation Context.
 * Circulation TIDAK boleh menyentuh model/tabel Catalog secara langsung.
 */
interface BookServiceInterface
{
    /** @return array{id:int,title:string,isbn:string,stock:int,is_active:bool}|null */
    public function getBookForLoan(int $bookId): ?array;

    public function reduceStock(int $bookId, int $quantity): bool;

    public function restoreStock(int $bookId, int $quantity): bool;

    /** @return list<array{id:int,title:string,isbn:string,stock:int}> */
    public function availableBooks(): array;
}
