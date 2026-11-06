<?php
declare(strict_types=1);

namespace App\Payments;

use App\Exceptions\InvalidAmountException;
use App\ValueObject\Invoice;

final class CardGateway extends AbstractPaymentGateway
{
    public function canProcess(Invoice $invoice): bool
    {
        return $invoice->method === 'card';
    }

    protected function validateInvoice(Invoice $invoice): void
    {
        if ($invoice->amount > 500000) {
            throw new InvalidAmountException('Превышен лимит разовой оплаты по карте (макс. 500,000).');
        }
    }

    protected function executePayment(Invoice $invoice): void
    {
        if ($invoice->studentId === 'BLOCKED') {
            throw new \RuntimeException('Карта студента заблокирована банком.');
        }
        printf("Процессинг карты: Списано %.2f у студента %s\n", $invoice->amount, $invoice->studentId);
    }
}
