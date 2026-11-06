<?php
declare(strict_types=1);

namespace App\ValueObject;

final readonly class PaymentReceipt
{
    public function __construct(
        public string $transactionId,
        public string $method,
        public \DateTimeImmutable $paidAt
    ) {}
}
