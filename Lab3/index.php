<?php
declare(strict_types=1); // Включение строгого режима типов

// Функция вывода декоративной линии через цикл while (требование ТЗ)
function printSeparator(int $length = 30): void {
    $i = 0;
    while ($i < $length) {
        echo "-";
        $i++;
    }
    echo "<br>";
}

$htmlOutput = '';
$errorOutput = '';
$isSubmitted = false;

// Обработка данных только при отправке формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['steps_input'])) {
    $isSubmitted = true;
    $inputString = $_POST['steps_input']; 
    
    // Разбиваем текст по переносу строки (каждая строка — новый день)
    $rawElements = explode("\n", str_replace("\r", "", $inputString));
    $stepsData = [];

    foreach ($rawElements as $el) {
        $el = trim($el);
        if ($el === '') {
            continue; // Игнорируем совсем пустые строки между Enter-ами
        }
        
        if (strtolower($el) === 'null') {
            $stepsData[] = null;
        } else {
            $stepsData[] = is_numeric($el) ? (int)$el : $el;
        }
    }

    $totalSteps = 0;
    $validDaysCount = 0;
    $maxSteps = 0;
    $logs = '';

    try {
        if ($stepsData === []) {
            throw new RuntimeException("Вы не ввели ни одного значения шагов.");
        }

        // Основной перебор массива через foreach
        foreach ($stepsData as $index => $steps) {
            if ($steps === null) {
                $logs .= "<div class='log-item'>День " . ($index + 1) . ": Нет данных (пропущено).</div>";
                continue;
            }

            if ($steps === -1) {
                $logs .= "<div class='log-item status-break'>Обнаружен маркер остановки (-1). Обработка завершена.</div>";
                break;
            }

            if (!is_int($steps) || $steps < 0 || $steps > 100000) {
                $displayValue = is_string($steps) ? "'$steps'" : var_export($steps, true);
                throw new InvalidArgumentException("Некорректное значение в строке " . ($index + 1) . ": {$displayValue}");
            }

            $totalSteps += $steps;
            $validDaysCount++;
            
            if ($steps > $maxSteps) {
                $maxSteps = $steps;
            }
        }

        if ($validDaysCount === 0) {
            throw new RuntimeException("Нет валидных данных для расчета итоговых показателей.");
        }

        $averageSteps = round($totalSteps / $validDaysCount, 2);

        $activityLevel = match (true) {
            $averageSteps >= 10000 => "Высокая активность",
            $averageSteps >= 7500  => "Умеренная активность",
            $averageSteps >= 5000  => "Низкая активность",
            default                => "Сидячий образ жизни"
        };

        // Формируем отдельные окна-карточки для результатов
        $htmlOutput = "
        <div class='log-container'>
            <h3>Ход обработки дней:</h3>
            {$logs}
        </div>
        
        <h3>Итоговые результаты:</h3>
        <div class='grid-container'>
            <div class='card'>
                <div class='card-title'>Обработано дней</div>
                <div class='card-value'>{$validDaysCount}</div>
            </div>
            <div class='card'>
                <div class='card-title'>Всего шагов</div>
                <div class='card-value'>{$totalSteps}</div>
            </div>
            <div class='card'>
                <div class='card-title'>В среднем за день</div>
                <div class='card-value'>{$averageSteps}</div>
            </div>
            <div class='card'>
                <div class='card-title'>Лучший результат</div>
                <div class='card-value'>{$maxSteps} за день</div>
            </div>
            <div class='card card-highlight'>
                <div class='card-title'>Уровень активности</div>
                <div class='card-value'>{$activityLevel}</div>
            </div>
        </div>";

    } catch (InvalidArgumentException $e) {
        $errorOutput = "<div class='error-box'><strong>Ошибка в данных:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    } catch (RuntimeException $e) {
        $errorOutput = "<div class='error-box'><strong>Системная ошибка:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Анализ физической активности</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background-color: #f4f6f9; color: #333; }
        h2, h3 { color: #2c3e50; }
        .form-container { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; max-width: 500px; }
        textarea { width: 100%; height: 150px; padding: 12px; font-size: 15px; border: 2px solid #dcdde1; border-radius: 6px; box-sizing: border-box; resize: none; font-family: inherit; }
        textarea:focus { border-color: #3498db; outline: none; }
        input[type="submit"] { margin-top: 15px; width: 100%; padding: 12px; font-size: 16px; background-color: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.2s; }
        input[type="submit"]:hover { background-color: #2980b9; }
        
        /* Модульная сетка окон для результатов */
        .grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 5px solid #3498db; text-align: center; }
        .card-highlight { border-top: 5px solid #2ecc71; background-color: #fafffa; }
        .card-title { font-size: 12px; text-transform: uppercase; color: #7f8c8d; font-weight: bold; margin-bottom: 12px; letter-spacing: 0.5px; }
        .card-value { font-size: 22px; font-weight: bold; color: #2c3e50; }
        
        /* Стили логов */
        .log-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; max-width: 600px; }
        .log-item { padding: 8px 0; border-bottom: 1px dashed #f1f2f6; font-size: 14px; }
        .status-break { color: #e67e22; font-weight: bold; }
        .error-box { background: #fce4e4; color: #c0392b; padding: 15px; border-radius: 8px; border-left: 5px solid #e74c3c; margin-top: 15px; max-width: 600px; font-weight: 500; }
        .footer-line { margin-top: 40px; font-size: 13px; color: #95a5a6; }
    </style>
</head>
<body>

<h2>Анализ физической активности (Вариант 12)</h2>

<div class="form-container">
    <form method="POST" action="">
        <label for="steps_input"><strong>Введите количество шагов (каждый день с новой строки):</strong></label><br><br>
        <!-- Поле ввода в виде большого окна, очищается при каждом обновлении -->
        <textarea id="steps_input" name="steps_input" placeholder="Пример ввода:&#10;8000&#10;12500&#10;null&#10;-1" required autocomplete="off"></textarea>
        <input type="submit" value="Запустить расчет">
    </form>
</div>

<?php 
if ($isSubmitted) {
    echo $errorOutput;
    echo $htmlOutput;
}
?>

<div class="footer-line">
    <?php printSeparator(60); ?>
    <p><em>Скрипт завершил работу контроля данных.</em></p>
</div>

</body>
</html>
