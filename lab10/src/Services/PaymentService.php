<?php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use App\ValueObject\Invoice;
use App\ValueObject\PaymentReceipt;

final class PaymentService
{
    /** @param list<PaymentGatewayInterface> $gateways */
    public function __construct(private array $gateways) {}

    public function pay(Invoice $invoice): PaymentReceipt
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->canProcess($invoice)) {
                return $gateway->process($invoice);
            }
        }

        throw new PaymentException('Подходящий способ оплаты не найден.', $invoice->method);
    }
}
