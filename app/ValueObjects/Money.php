<?php

namespace App\ValueObjects;

class Money
{
    private string $amount;

    public function __construct(string $amount)
    {
        $this->amount = $amount;
    }

    public function add(self $other): self
    {
        return new self(
            bcadd($this->amount, $other->amount, 2)
        );
    }

    public function subtract(self $other): self
    {
        return new self(
            bcsub($this->amount, $other->amount, 2)
        );
    }

    public function multiply(string|int $factor): self
    {
        return new self(
            bcmul($this->amount, (string) $factor, 2)
        );
    }

    public function divide(string|int $divisor): self
    {
        return new self(
            bcdiv($this->amount, (string) $divisor, 2)
        );
    }

    public function percentage(self $base): self
    {
        if ($base->isZero()) {
            return new self('0');
        }

        return new self(
            bcmul(bcdiv($this->amount, $base->amount, 6), '100', 2)
        );
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', 2) === 0;
    }

    public function get(): string
    {
        return $this->amount;
    }
}
