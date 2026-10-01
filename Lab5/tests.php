<?php
declare(strict_types=1);

require_once 'catalog.php';
require_once 'functions.php';

echo "<h2>Запуск автоматического тестирования (10 сценариев)</h2>";
echo "<table border='1' cellpadding='6' cellspacing='0'>
<tr><th>№</th><th>Сценарий</th><th>Ожидаемый результат</th><th>Фактический результат</th><th>Статус</th></tr>";

function runTest(int $num, string $title, callable $test, string $expected): void {
    try {
        $result = $test();
        $passed = ($result === $expected);
        $status = $passed ? "<span style='color:green;'>PASSED</span>" : "<span style='color:red;'>FAILED</span>";
        echo "<tr><td>{$num}</td><td>{$title}</td><td>" . e($expected) . "</td><td>" . e($result) . "</td><td>{$status}</td></tr>";
    } catch (Throwable $e) {
        $actual = get_class($e) . ": " . $e->getMessage();
        $passed = (str_contains($actual, $expected));
        $status = $passed ? "<span style='color:green;'>PASSED</span>" : "<span style='color:red;'>FAILED</span>";
        echo "<tr><td>{$num}</td><td>{$title}</td><td>" . e($expected) . "</td><td>" . e($actual) . "</td><td>{$status}</td></tr>";
    }
}

// Тест 1
runTest(1, "Корректный каталог", function() use ($products) {
    array_walk($products, fn($p) => validateProduct($p));
    return "OK";
}, "OK");

// Тест 2
runTest(2, "Поиск в разном регистре", function() use ($products) {
    $res1 = searchProducts($products, "гитара");
    $res2 = searchProducts($products, "ГИТара");
    return count($res1) === count($res2) ? "EQUAL" : "NOT_EQUAL";
}, "EQUAL");

// Тест 3
runTest(3, "Лишние пробелы в названии", function() {
    return normalizeText("   Гитара   акустическая   ");
}, "гитара акустическая");

// Тест 4
runTest(4, "Пустой результат фильтра", function() use ($products) {
    $res = filterByManufacturer($products, "NonExistentBrand");
    return count($res);
}, "0");

// Тест 5
runTest(5, "Граничная цена 0", function() {
    $item = ["id" => 1, "name" => "Бесплатно", "category" => "Тест", "price" => 0.0, "stock" => 1, "created_at" => "2026-01-01", "type" => "T", "manufacturer" => "M"];
    validateProduct($item);
    return "VALID";
}, "VALID");

// Тест 6
runTest(6, "Отрицательная цена", function() {
    $item = ["id" => 1, "name" => "Ошибка", "category" => "Тест", "price" => -100.0, "stock" => 1, "created_at" => "2026-01-01", "type" => "T", "manufacturer" => "M"];
    validateProduct($item);
    return "VALID";
}, "InvalidArgumentException: Некорректная цена.");

// Тест 7
runTest(7, "Отсутствующий ключ manufacturer", function() {
    $item = ["id" => 1, "name" => "Ошибка", "category" => "Тест", "price" => 100.0, "stock" => 1, "created_at" => "2026-01-01", "type" => "T"];
    validateProduct($item);
    return "VALID";
}, "InvalidArgumentException: Отсутствует обязательное поле: manufacturer");

// Тест 8
runTest(8, "Некорректная дата", function() {
    $item = ["id" => 1, "name" => "Тест", "category" => "Тест", "price" => 100.0, "stock" => 1, "created_at" => "2026-13-45", "type" => "T", "manufacturer" => "M"];
    validateProduct($item);
    return "VALID";
}, "InvalidArgumentException: Некорректная дата добавления (ожидается YYYY-MM-DD).");

// Тест 9
runTest(9, "Защита от XSS (htmlspecialchars)", function() {
    return e("<script>alert(1)</script>");
}, "&lt;script&gt;alert(1)&lt;/script&gt;");

// Тест 10
runTest(10, "Сортировка одинаковых цен", function() {
    $items = [
        ["id" => 1, "price" => 100.0],
        ["id" => 2, "price" => 100.0]
    ];
    $sorted = sortByPrice($items, true);
    return count($sorted);
}, "2");

echo "</table>";