<?php
declare(strict_types=1);

/**
 * Валидация параметров роста и веса.
 * @throws InvalidArgumentException
 */
function validateMeasurements(float $weight, float $height): void
{
    if ($weight <= 2.0 || $weight > 500.0) {
        throw new InvalidArgumentException("Вес должен быть в пределах от 2 до 500 кг.");
    }
    if ($height <= 0.4 || $height > 3.0) {
        throw new InvalidArgumentException("Рост должен быть в пределах от 0.4 до 3.0 метров.");
    }
}

/**
 * Расчет ИМТ (BMI). Параметр по умолчанию $precision.
 */
function computeBMI(float $weight, float $height, int $precision = 1): float
{
    validateMeasurements($weight, $height);
    return round($weight / ($height ** 2), $precision);
}

/**
 * Определение категории веса.
 */
function getBMICategory(float $bmi): string
{
    if ($bmi <= 0) {
        throw new RuntimeException("Недопустимое значение ИМТ.");
    }
    return match (true) {
        $bmi < 16.0  => "Выраженный дефицит массы",
        $bmi < 18.5  => "Недостаточная масса тела",
        $bmi < 25.0  => "Нормальный вес",
        $bmi < 30.0  => "Избыточная масса (предожирение)",
        $bmi < 35.0  => "Ожирение 1 степени",
        $bmi < 40.0  => "Ожирение 2 степени",
        default      => "Ожирение 3 степени",
    };
}

/**
 * Получение медицинских рекомендаций с Union-типом для кастомизации под пол.
 */
function getHealthRecommendations(string $category, ?string $gender = null): string
{
    if (trim($category) === "") {
        throw new InvalidArgumentException("Категория не может быть пустой.");
    }

    $suffix = "";
    if ($gender !== null) {
        $g = mb_strtolower(trim($gender));
        if ($g === "м" || $g === "male") {
            $suffix = " Учтите мужской профиль распределения нагрузок.";
        } elseif ($g === "ж" || $g === "female") {
            $suffix = " Учтите женские особенности обмена веществ.";
        }
    }

    return match ($category) {
        "Выраженный дефицит массы", "Недостаточная масса тела" => "Рекомендуется повысить калорийность питания." . $suffix,
        "Нормальный вес" => "Прекрасно! Поддерживайте текущий баланс активности и питания.",
        "Избыточная масса (предожирение)" => "Рекомендуется ограничить простые углеводы." . $suffix,
        "Ожирение 1 степени", "Ожирение 2 степени", "Ожирение 3 степени" => "Необходима консультация диетолога." . $suffix,
        default => "Обратитесь к специалисту.",
    };
}

/**
 * Форматирование финального отчета с Union-типом (int|float) для BMI.
 */
function formatBMIReport(string $name, int|float $bmi, string $category, string $recommendation): string
{
    if (trim($name) === "") {
        throw new InvalidArgumentException("Имя обязательно.");
    }
    $formattedBmi = is_float($bmi) ? sprintf("%.1f", $bmi) : (string)$bmi;
    return "Пациент: {$name} | ИМТ: {$formattedBmi} | {$category}. {$recommendation}";
}

/**
 * Универсальная агрегирующая функция (Повышенная сложность).
 */
function summarize(array $items, callable $selector): array
{
    if ($items === []) {
        throw new InvalidArgumentException("Массив пуст.");
    }
    $values = array_map($selector, $items);
    return [
        'min' => min($values),
        'max' => max($values),
        'avg' => round(array_sum($values) / count($values), 1)
    ];
}
