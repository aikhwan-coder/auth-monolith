<?php

use Modules\Shared\ValueObjects\Denda;
use Modules\Shared\ValueObjects\Isbn;

it('menerima ISBN-13 yang valid dan menormalkan tanda hubung', function () {
    expect((new Isbn('978-0-306-40615-7'))->getValue())->toBe('9780306406157');
});

it('menerima ISBN-10 yang valid', function () {
    expect((new Isbn('0-306-40615-2'))->getValue())->toBe('0306406152');
});

it('menolak ISBN dengan format atau checksum salah', function (string $isbn) {
    new Isbn($isbn);
})->with(['123', '9780306406158', 'abcdefghij', ''])->throws(InvalidArgumentException::class);

it('menghitung denda Rp 1.000 per hari keterlambatan', function () {
    $denda = Denda::hitung(new DateTimeImmutable('2026-11-01'), new DateTimeImmutable('2026-11-04'));

    expect($denda->getHariTerlambat())->toBe(3)
        ->and($denda->getJumlah())->toBe(3000)
        ->and((string) $denda)->toBe('Rp 3.000')
        ->and($denda->isTerlambat())->toBeTrue();
});

it('tidak ada denda jika dikembalikan sebelum atau tepat pada jatuh tempo', function () {
    $tepat = Denda::hitung(new DateTimeImmutable('2026-11-10'), new DateTimeImmutable('2026-11-10'));
    $awal = Denda::hitung(new DateTimeImmutable('2026-11-10'), new DateTimeImmutable('2026-11-08'));

    expect($tepat->getJumlah())->toBe(0)
        ->and($awal->getJumlah())->toBe(0)
        ->and($awal->isTerlambat())->toBeFalse();
});

it('menolak tarif denda negatif', function () {
    Denda::hitung(new DateTimeImmutable('2026-11-01'), new DateTimeImmutable('2026-11-04'), -1);
})->throws(InvalidArgumentException::class);
