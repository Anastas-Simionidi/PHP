<?php
declare(strict_types=1);

namespace App\Exceptions;

final class PaymentException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $method,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
