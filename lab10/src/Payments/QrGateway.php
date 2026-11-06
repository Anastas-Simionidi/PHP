<?php
declare(strict_types=1);

namespace App\Payments;

use App\Exceptions\InvalidAmountException;
use App\ValueObject\Invoice;

final class QrGateway extends AbstractPaymentGateway
{
    public function canProcess(Invoice $invoice): bool
    {
        return $invoice->method === 'qr';
    }

    protected function validateInvoice(Invoice $invoice): void
    {
        if ($invoice->amount < 1000) {
            throw new InvalidAmountException('Минимальная сумма для оплаты по QR — 1,000.');
        }
    }

    protected function executePayment(Invoice $invoice): void
    {
        if ($invoice->studentId === 'EXPIRED') {
            throw new \RuntimeException('Время действия QR-кода истекло.');
        }
        printf("Процессинг QR: Сканирование кода на сумму %.2f для %s\n", $invoice->amount, $invoice->studentId);
    }
}
