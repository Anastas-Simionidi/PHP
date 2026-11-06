<?php
declare(strict_types=1);

namespace App\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use App\Support\LogsActivity;
use App\ValueObject\Invoice;
use App\ValueObject\PaymentReceipt;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    use LogsActivity;

    final public function process(Invoice $invoice): PaymentReceipt
    {
        if (!$this->canProcess($invoice)) {
            throw new PaymentException('Метод оплаты не поддерживается шлюзом.', $invoice->method);
        }

        $this->validateInvoice($invoice);

        try {
            $this->executePayment($invoice);
            $this->log("Успешная оплата на сумму {$invoice->amount} для студента {$invoice->studentId}");

            return new PaymentReceipt(
                'TXN-' . bin2hex(random_bytes(4)),
                $invoice->method,
                new \DateTimeImmutable()
            );
        } catch (PaymentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new PaymentException('Критическая ошибка процессинга.', $invoice->method, $e);
        }
    }

    abstract protected function validateInvoice(Invoice $invoice): void;
    abstract protected function executePayment(Invoice $invoice): void;
}
