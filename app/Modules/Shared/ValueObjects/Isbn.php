<?php

namespace Modules\Shared\ValueObjects;

use InvalidArgumentException;

final class Isbn
{
    private string $value;

    public function __construct(string $value)
    {
        $clean = strtoupper((string) preg_replace('/[\s-]/', '', $value));

        if (! self::isValid($clean)) {
            throw new InvalidArgumentException("Format ISBN tidak valid: {$value}");
        }

        $this->value = $clean;
    }

    public static function isValid(string $isbn): bool
    {
        // ISBN-10: 9 digit + 1 digit/X, checksum modulo 11
        if (preg_match('/^\d{9}[\dX]$/', $isbn)) {
            $sum = 0;
            for ($i = 0; $i < 10; $i++) {
                $digit = $isbn[$i] === 'X' ? 10 : (int) $isbn[$i];
                $sum += $digit * (10 - $i);
            }

            return $sum % 11 === 0;
        }

        // ISBN-13: 13 digit, checksum modulo 10 (bobot 1 dan 3)
        if (preg_match('/^\d{13}$/', $isbn)) {
            $sum = 0;
            for ($i = 0; $i < 13; $i++) {
                $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
            }

            return $sum % 10 === 0;
        }

        return false;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
