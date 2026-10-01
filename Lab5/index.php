<?php
declare(strict_types=1);

require_once 'catalog.php';
require_once 'functions.php';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа №5 — Вариант 16</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .alert { padding: 10px; background-color: #f8d7da; color: #721c24; margin-bottom: 15px; }
        .info { padding: 10px; background-color: #d1ecf1; color: #0c5460; margin-bottom: 15px; }
    </style>
</head>
<body>

<h1>Каталог музыкальных инструментов (Вариант 16)</h1>

<?php
try {
    // 1. Валидация каталога
    array_walk($products, fn(array $p) => validateProduct($p));

    // 2. Выполнение обязательной обработки по Варианту 16
    // - Поиск по производителю "Yamaha"
    // - Ограничение по наличию
    $filtered = filterByManufacturer($products, "Yamaha", true);

    // - Применение скидки 10% через array_map
    $discounted = applyDiscount($filtered, 0.10);

    // - Сортировка по цене по возрастанию через usort
    $sorted = sortByPrice($discounted, true);

    // - Вычисление минимальной цены через array_reduce
    $minPrice = getMinPrice($products);

    echo "<h2>1. Фильтрация (Производитель: Yamaha, В наличии), Скидка 10%, Сортировка по цене</h2>";
    echo renderTable($sorted);

    echo "<div class='info'>";
    echo "<strong>Минимальная цена товара в полном каталоге:</strong> " . number_format($minPrice, 2, '.', ' ') . " тг.";
    echo "</div>";

    // 3. Выполнение Дополнительного задания (groupBy)
    echo "<h2>2. Статистика по категориям (Дополнительное задание)</h2>";
    $grouped = groupBy($products, fn(array $p) => $p["category"]);

    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Категория</th><th>Кол-во позиций</th><th>Общий остаток</th><th>Минимальная цена</th></tr>";
    foreach ($grouped as $category => $items) {
        $count = count($items);
        $totalStock = array_reduce($items, fn(int $sum, array $p) => $sum + (int)$p["stock"], 0);
        $categoryMinPrice = getMinPrice($items);
        echo "<tr>";
        echo "<td>" . e((string)$category) . "</td>";
        echo "<td>{$count}</td>";
        echo "<td>{$totalStock}</td>";
        echo "<td>" . number_format($categoryMinPrice, 2, '.', ' ') . " тг</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (InvalidArgumentException $e) {
    echo "<div class='alert'>Ошибка валидации данных: " . e($e->getMessage()) . "</div>";
}
?>

</body>
</html>