<?php
declare(strict_types=1);

// --- Индивидуальное задание: Вариант 1 (Динамический расчет) ---
// Константа скидки по умолчанию (если форма пуста или для базового расчета)
const DEFAULT_DISCOUNT = 10.0;

// Инициализируем переменные данными из формы (POST-запрос) или дефолтными значениями
$itemName = isset($_POST['item_name']) ? htmlspecialchars((string)$_POST['item_name']) : "Беспроводные наушники";
$price = isset($_POST['price']) ? (float)$_POST['price'] : 25000.50;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 3;
$discount = isset($_POST['discount']) ? (float)$_POST['discount'] : DEFAULT_DISCOUNT;

// Арифметическое выражение для расчета итоговой стоимости
$totalCost = $price * $quantity * (1 - $discount / 100);

// Использование строгого сравнения для определения статуса заказа
$isZeroCost = $totalCost === 0.0;
$statusText = $totalCost >= 50000.0 ? "Крупный коммерческий заказ" : "Стандартный розничный заказ";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная работа №2 - Вариант 1 (Интерактивный)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background-color: #f9f9f9; color: #333; }
        .container { max-width: 600px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin: 0 auto; }
        h1, h3 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 8px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #3498db; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; font-weight: bold; }
        button:hover { background-color: #2980b9; }
        .result-box { background-color: #e8f4fd; border-left: 5px solid #3498db; padding: 15px; margin-top: 20px; border-radius: 4px; }
        .badge { display: inline-block; padding: 5px 10px; background: #e67e22; color: #fff; border-radius: 4px; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h1>Расчет стоимости товара</h1>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="item_name">Наименование товара:</label>
            <input type="text" id="item_name" name="item_name" value="<?= $itemName ?>" required>
        </div>
        
        <div class="form-group">
            <label for="price">Цена товара (₸):</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= $price ?>" required>
        </div>
        
        <div class="form-group">
            <label for="quantity">Количество (шт.):</label>
            <input type="number" id="quantity" name="quantity" min="1" value="<?= $quantity ?>" required>
        </div>
        
        <div class="form-group">
            <label for="discount">Скидка (%):</label>
            <input type="number" id="discount" name="discount" step="0.1" min="0" max="100" value="<?= $discount ?>" required>
        </div>
        
        <button type="submit">Пересчитать стоимость</button>
    </form>

    <div class="result-box">
        <h3>Результаты расчета:</h3>
        <p><strong>Товар:</strong> <?= $itemName ?></p>
        <p><strong>Базовая стоимость:</strong> <?= number_format($price * $quantity, 2, ".", " ") ?> ₸</p>
        <p><strong>Размер скидки:</strong> <?= $discount ?>% (<?= number_format(($price * $quantity) * ($discount / 100), 2, ".", " ") ?> ₸)</p>
        <p><strong>Итого к оплате:</strong> <span style="font-size: 20px; color: #27ae60; font-weight: bold;"><?= number_format($totalCost, 2, ".", " ") ?> ₸</span></p>
        <p><strong>Категория заказа:</strong> <span class="badge"><?= $statusText ?></span></p>
    </div>
</div>

</body>
</html>
