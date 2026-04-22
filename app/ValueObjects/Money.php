<?php

namespace App\ValueObjects;

class Money
{
    private const INTERNAL_SCALE = 6;
    private const OUTPUT_SCALE = 2;

    private string $amount;

    public function __construct(string $amount)
    {
        $this->amount = $amount;
    }

    public function add(self $other): self
    {
        return new self(
            bcadd($this->amount, $other->amount, self::INTERNAL_SCALE)
        );
    }

    public function subtract(self $other): self
    {
        return new self(
            bcsub($this->amount, $other->amount, self::INTERNAL_SCALE)
        );
    }

    public function multiply(string|int $factor): self
    {
        return new self(
            bcmul($this->amount, (string) $factor, self::INTERNAL_SCALE)
        );
    }

    public function divide(string|int $divisor): self
    {
        return new self(
            bcdiv($this->amount, (string) $divisor, self::INTERNAL_SCALE)
        );
    }

    public function percentage(self $base): self
    {
        if ($base->isZero()) {
            return new self('0');
        }

        return new self(
            bcmul(bcdiv($this->amount, $base->amount, self::INTERNAL_SCALE), '100', self::INTERNAL_SCALE)
        );
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', self::INTERNAL_SCALE) === 0;
    }

    public function get(): string
    {
        return bcadd($this->amount, '0', self::OUTPUT_SCALE);
    }
}
