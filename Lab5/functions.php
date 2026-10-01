<?php
declare(strict_types=1);

// 1. Нормализация текста (UTF-8, пробелы, регистр)
function normalizeText(string $value): string
{
    $value = trim($value);
    $value = preg_replace("/\s+/u", " ", $value) ?? $value;
    return mb_strtolower($value, "UTF-8");
}

// 2. Экранирование HTML
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// 3. Валидация одной записи товара
function validateProduct(array $product): void
{
    $required = ["id", "name", "category", "price", "stock", "created_at", "type", "manufacturer"];
    foreach ($required as $key) {
        if (!array_key_exists($key, $product)) {
            throw new InvalidArgumentException("Отсутствует обязательное поле: {$key}");
        }
    }

    if (!is_int($product["id"]) || $product["id"] <= 0) {
        throw new InvalidArgumentException("Некорректный ID товара.");
    }
    if (trim((string)$product["name"]) === "") {
        throw new InvalidArgumentException("Пустое наименование товара.");
    }
    if (!is_numeric($product["price"]) || (float)$product["price"] < 0) {
        throw new InvalidArgumentException("Некорректная цена.");
    }
    if (!is_int($product["stock"]) || $product["stock"] < 0) {
        throw new InvalidArgumentException("Некорректный остаток на складе.");
    }

    $date = DateTimeImmutable::createFromFormat("!Y-m-d", (string)$product["created_at"]);
    if ($date === false || $date->format("Y-m-d") !== $product["created_at"]) {
        throw new InvalidArgumentException("Некорректная дата добавления (ожидается YYYY-MM-DD).");
    }
}

// 4. Поиск по названию (mb_stripos)
function searchProducts(array $items, string $query): array
{
    $query = normalizeText($query);
    if ($query === "") {
        return $items;
    }
    return array_values(array_filter($items, fn(array $p): bool =>
        mb_stripos(normalizeText((string)$p["name"]), $query, 0, "UTF-8") !== false
    ));
}

// 5. Фильтрация по производителю и наличию
function filterByManufacturer(array $items, ?string $manufacturer = null, bool $onlyAvailable = false): array
{
    $normManufacturer = $manufacturer !== null ? normalizeText($manufacturer) : null;

    return array_values(array_filter($items, function (array $p) use ($normManufacturer, $onlyAvailable): bool {
        if ($normManufacturer !== null && normalizeText((string)$p["manufacturer"]) !== $normManufacturer) {
            return false;
        }
        if ($onlyAvailable && (int)$p["stock"] <= 0) {
            return false;
        }
        return true;
    }));
}

// 6. Поиск минимальной цены (array_reduce)
function getMinPrice(array $items): float
{
    if (empty($items)) {
        return 0.0;
    }
    return array_reduce($items, function (float $min, array $p): float {
        $price = (float)$p["price"];
        return ($min === 0.0 || $price < $min) ? $price : $min;
    }, 0.0);
}

// 7. Сортировка по цене (usort)
function sortByPrice(array $items, bool $ascending = true): array
{
    usort($items, fn(array $a, array $b): int => $ascending
        ? $a["price"] <=> $b["price"]
        : $b["price"] <=> $a["price"]
    );
    return $items;
}

// 8. Расчет скидки (array_map)
function applyDiscount(array $items, float $rate): array
{
    if ($rate < 0 || $rate > 1) {
        throw new InvalidArgumentException("Неверная ставка скидки (должна быть от 0.0 до 1.0).");
    }
    return array_map(function (array $p) use ($rate): array {
        $p["final_price"] = round((float)$p["price"] * (1 - $rate), 2);
        return $p;
    }, $items);
}

// 9. Отрисовка HTML-таблицы с экранированием
function renderTable(array $items): string
{
    if (empty($items)) {
        return "<p><i>Товары не найдены.</i></p>";
    }

    $html = "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
    $html .= "<thead><tr>
                <th>ID</th>
                <th>Название</th>
                <th>Категория</th>
                <th>Тип</th>
                <th>Производитель</th>
                <th>Цена (тг)</th>
                <th>Скидка (тг)</th>
                <th>Остаток</th>
              </tr></thead><tbody>";

    foreach ($items as $p) {
        $finalPrice = isset($p["final_price"]) ? number_format((float)$p["final_price"], 2, ".", " ") : "-";
        $html .= "<tr>"
            . "<td>" . (int)$p["id"] . "</td>"
            . "<td>" . e((string)$p["name"]) . "</td>"
            . "<td>" . e((string)$p["category"]) . "</td>"
            . "<td>" . e((string)$p["type"]) . "</td>"
            . "<td>" . e((string)$p["manufacturer"]) . "</td>"
            . "<td>" . number_format((float)$p["price"], 2, ".", " ") . "</td>"
            . "<td>" . $finalPrice . "</td>"
            . "<td>" . (int)$p["stock"] . "</td>"
            . "</tr>";
    }

    $html .= "</tbody></table>";
    return $html;
}

// Дополнительное задание повышенной сложности: Функция groupBy
function groupBy(array $items, callable $keySelector): array
{
    $result = [];
    foreach ($items as $item) {
        $key = $keySelector($item);
        $result[$key][] = $item;
    }
    return $result;
}