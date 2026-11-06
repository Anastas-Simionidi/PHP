<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Exceptions\PaymentException;
use App\Exceptions\InvalidAmountException;
use App\Services\PaymentService;
use App\Payments\CardGateway as CreditCardGateway; // Псевдоним 'as'
use App\Payments\QrGateway;
use App\ValueObject\Invoice;

$service = new PaymentService([
    new CreditCardGateway(),
    new QrGateway()
]);

try {
    $receipt = $service->pay(new Invoice('ST-2026', 150000.0, 'card'));
    echo "Оплата принята! ID транзакции: {$receipt->transactionId}\n";
} catch (InvalidAmountException $e) {
    echo "Ошибка данных: " . $e->getMessage() . PHP_EOL;
} catch (PaymentException $e) {
    error_log("Журнал ошибок: " . $e->__toString());
    echo "Не удалось выполнить платеж через канал: " . $e->method . PHP_EOL;
} catch (\Throwable $e) {
    echo "Системная ошибка: " . $e->getMessage() . PHP_EOL;
} finally {
    echo "Работа сценария завершена.\n";
}
