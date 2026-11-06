<?php
declare(strict_types=1);

namespace App\Contracts;

use App\ValueObject\Invoice;
use App\ValueObject\PaymentReceipt;

interface PaymentGatewayInterface
{
    public function canProcess(Invoice $invoice): bool;
    public function process(Invoice $invoice): PaymentReceipt;
}
