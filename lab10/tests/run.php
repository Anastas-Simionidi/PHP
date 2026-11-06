<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Services\PaymentService;
use App\Payments\CardGateway;
use App\Payments\QrGateway;
use App\ValueObject\Invoice;
use App\Exceptions\InvalidAmountException;
use App\Exceptions\PaymentException;

function runTest(string $name, callable $test): void
{
    try {
        $test();
        echo "✅ OK: {$name}\n";
    } catch (\Throwable $e) {
        echo "❌ FAIL: {$name} — Выброшено: " . $e::class . " (Сообщение: " . $e->getMessage() . ")\n";
    }
}

$card = new CardGateway();
$qr = new QrGateway();
$service = new PaymentService([$card, $qr]);

// Тест 1: Успешная оплата по карте
runTest('1. Успешная оплата по карте', function() use ($service) {
    $receipt = $service->pay(new Invoice('ST-001', 50000, 'card'));
    assert($receipt->method === 'card');
});

// Тест 2: Успешная оплата по QR
runTest('2. Успешная оплата по QR', function() use ($service) {
    $receipt = $service->pay(new Invoice('ST-002', 15000, 'qr'));
    assert($receipt->method === 'qr');
});

// Тест 3: Превышение лимита по карте
runTest('3. Превышение лимита по карте', function() use ($service) {
    try {
        $service->pay(new Invoice('ST-003', 600000, 'card'));
    } catch (InvalidAmountException $e) {
        return; // Тест пройден
    }
    throw new \Exception('Исключение InvalidAmountException не было выброшено');
});

// Тест 4: Сумма QR ниже минимальной
runTest('4. Сумма QR ниже минимальной', function() use ($service) {
    try {
        $service->pay(new Invoice('ST-004', 500, 'qr'));
    } catch (InvalidAmountException $e) {
        return;
    }
    throw new \Exception('Исключение InvalidAmountException не было выброшено');
});

// Тест 5: Неизвестный канал оплаты
runTest('5. Неизвестный канал оплаты', function() use ($service) {
    try {
        $service->pay(new Invoice('ST-005', 2000, 'crypto'));
    } catch (PaymentException $e) {
        assert($e->method === 'crypto');
        return;
    }
    throw new \Exception('Исключение PaymentException не было выброшено');
});

// Тест 6: Имитация банковского сбоя (сохранение previous)
runTest('6. Имитация банковского сбоя (Card)', function() use ($service) {
    try {
        $service->pay(new Invoice('BLOCKED', 2000, 'card'));
    } catch (PaymentException $e) {
        assert($e->getPrevious() instanceof \RuntimeException);
        return;
    }
    throw new \Exception('Предыдущее исключение не сохранилось');
});

// Тест 7: Имитация устаревшего кода (Qr)
runTest('7. Имитация устаревшего QR-кода', function() use ($service) {
    try {
        $service->pay(new Invoice('EXPIRED', 5000, 'qr'));
    } catch (PaymentException $e) {
        assert($e->getPrevious() !== null);
        return;
    }
    throw new \Exception('Ошибка выполнения QR не обработана');
});

// Тест 8: Проверка записей логов (Trait logEntries)
runTest('8. Проверка записей логов из Трейта', function() use ($card) {
    $entries = $card->logEntries();
    assert(count($entries) > 0, 'Логи шлюза пусты!');
});

// Тест 9: Проверка выполнения блока finally
runTest('9. Проверка выполнения блока finally', function() {
    $finallyExecuted = false;
    try {
        throw new \Exception('Test');
    } catch (\Throwable $e) {
        // Ошибка перехвачена
    } finally {
        $finallyExecuted = true;
    }
    assert($finallyExecuted, 'Блок finally не выполнился!');
});
