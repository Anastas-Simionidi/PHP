<?php
declare(strict_types=1);

// Подключаем библиотеку функций
require_once 'functions.php';

// Устанавливаем заголовок, чтобы текст в браузере отображался корректно
header('Content-Type: text/plain; charset=utf-8');

echo "=== ЗАПУСК АВТОМАТИЧЕСКОГО ТЕСТИРОВАНИЯ ЯДРА (ВАРИАНТ 11) ===\n";
echo "---------------------------------------------------------\n\n";

// Массив тестовых сценариев (минимум 8 штук по регламенту)
$scenarios = [
    1 => [
        'desc' => 'Обычные корректные параметры (Норма веса)',
        'run' => fn() => computeBMI(75.0, 1.75) === 24.5
    ],
    2 => [
        'desc' => 'Нижняя допустимая граница параметров (Ребенок/Миниатюрный человек)',
        'run' => function() { 
            validateMeasurements(2.5, 0.45); 
            return true; 
        }
    ],
    3 => [
        'desc' => 'Верхняя допустимая граница параметров (Крайняя степень ожирения)',
        'run' => function() { 
            validateMeasurements(495.0, 2.95); 
            return true; 
        }
    ],
    4 => [
        'desc' => 'Исключение: Ошибка валидации веса (Вес ниже нормы)',
        'run' => function() {
            try { 
                validateMeasurements(1.0, 1.80); 
                return false; // Если исключение не выброшено — тест провален
            } catch (InvalidArgumentException $e) { 
                return !empty($e->getMessage()); 
            }
        }
    ],
    5 => [
        'desc' => 'Исключение: Ошибка валидации роста (Рост гиганта)',
        'run' => function() {
            try { 
                validateMeasurements(80.0, 3.2); 
                return false; 
            } catch (InvalidArgumentException $e) { 
                return !empty($e->getMessage()); 
            }
        }
    ],
    6 => [
        'desc' => 'Исключение: Ошибка пустого массива в функции summarize',
        'run' => function() {
            try { 
                summarize([], fn($x) => $x); 
                return false; 
            } catch (InvalidArgumentException $e) { 
                return !empty($e->getMessage()); 
            }
        }
    ],
    7 => [
        'desc' => 'Проверка работы Nullable-типа (Передача null вместо пола)',
        'run' => fn() => getHealthRecommendations("Нормальный вес", null) !== ''
    ],
    8 => [
        'desc' => 'Проверка strict_types (Передача строки вызовет TypeError)',
        'run' => function() {
            try {
                // Вызываем динамически через имя переменной, чтобы IDE не подсвечивала ошибку до запуска
                $functionName = 'computeBMI';
                $functionName("недопустимая строка", 1.70);
                return false; 
            } catch (TypeError $e) { 
                return true; // Исключение TypeError успешно поймано
            }
        }
    ],
    9 => [
        'desc' => 'Проверка работы анонимного замыкания на смещение значений веса',
        'run' => function() {
            $delta = 1.2; // Поправка на вес одежды
            $closure = fn(float $v) => round($v - $delta, 1);
            return $closure(81.2) === 80.0;
        }
    ]
];

$passedCount = 0;

// Цикл автоматического выполнения тестов
foreach ($scenarios as $index => $test) {
    try {
        if (($test['run'])() === true) {
            echo "Тест №{$index} [{$test['desc']}]: ПРОЙДЕН (PASSED)\n";
            $passedCount++;
        } else {
            echo "Тест №{$index} [{$test['desc']}]: СБОЙ (FAILED)\n";
        }
    } catch (Throwable $t) {
        echo "Тест №{$index} [{$test['desc']}]: КРИТИЧЕСКОЕ ИСКЛЮЧЕНИЕ -> " . $t->getMessage() . "\n";
    }
}

echo "\n---------------------------------------------------------\n";
echo "ИТОГОВЫЙ РЕЗУЛЬТАТ: Успешно выполнено {$passedCount} из " . count($scenarios) . " тестов.\n";
