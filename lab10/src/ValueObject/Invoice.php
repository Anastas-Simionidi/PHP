<?php
declare(strict_types=1);

namespace App\ValueObject;

final readonly class Invoice
{
    public function __construct(
        public string $studentId,
        public float $amount,
        public string $method // 'card' или 'qr'
    ) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Сумма оплаты должна быть больше нуля.');
        }
    }
}
