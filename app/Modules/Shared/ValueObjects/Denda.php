<?php

namespace Modules\Shared\ValueObjects;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Value Object denda keterlambatan.
 * Dihitung otomatis dari tanggal jatuh tempo dan tanggal pengembalian.
 */
final class Denda
{
    public const TARIF_PER_HARI = 1000; // Rupiah per hari

    private function __construct(
        private int $hariTerlambat,
        private int $tarifPerHari,
    ) {}

    public static function hitung(
        DateTimeInterface $jatuhTempo,
        DateTimeInterface $tanggalKembali,
        int $tarifPerHari = self::TARIF_PER_HARI,
    ): self {
        if ($tarifPerHari < 0) {
            throw new InvalidArgumentException('Tarif denda tidak boleh negatif');
        }

        // Bandingkan per tanggal (jam diabaikan)
        $due = new DateTimeImmutable($jatuhTempo->format('Y-m-d'));
        $returned = new DateTimeImmutable($tanggalKembali->format('Y-m-d'));
        $diff = $due->diff($returned);

        $hari = $diff->invert === 1 ? 0 : (int) $diff->days;

        return new self($hari, $tarifPerHari);
    }

    public static function nol(): self
    {
        return new self(0, self::TARIF_PER_HARI);
    }

    public function getHariTerlambat(): int
    {
        return $this->hariTerlambat;
    }

    public function getTarifPerHari(): int
    {
        return $this->tarifPerHari;
    }

    public function getJumlah(): int
    {
        return $this->hariTerlambat * $this->tarifPerHari;
    }

    public function isTerlambat(): bool
    {
        return $this->hariTerlambat > 0;
    }

    public function __toString(): string
    {
        return 'Rp '.number_format($this->getJumlah(), 0, ',', '.');
    }
}
