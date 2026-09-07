<?php
// 1. Объявление констант (требование задания)
const UNIVERSITY = "Алматинский технологический университет";
const DISCIPLINE = "Программирование на PHP";

// 2. Данные студента (замените на свои)
$studentName = "Анастас Симиониди";           // Тип: string (строка)
$group = "ИС-24-22";                    // Тип: string (строка)
$course = 3;                            // Тип: int (целое число)
$variant = 1;                           // Тип: int (целое число)

// 3. Исходные данные для Варианта №1 (три оценки)
$grade1 = 90;                           // Тип: int
$grade2 = 75;                           // Тип: int
$grade3 = 80;                           // Тип: int

// 4. Вычисление среднего балла (арифметическая операция)
$result = ($grade1 + $grade2 + $grade3) / 3; // Тип: float (дробное число)

// 5. Условная конструкция if...else для определения статуса
if ($result >= 50) {
    $status = "Дисциплина освоена";
    $statusClass = "success";           // Класс для зеленого цвета в CSS
} else {
    $status = "Необходимо повысить результат";
    $statusClass = "warning";           // Класс для красного цвета в CSS
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа №1</title>
    <style>
        /* Стили оформления из требований к интерфейсу */
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #f4f6f8;
        }
        .card {
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(199, 141, 55, 0.1);
        }
        .success { color: green; font-weight: bold; }
        .warning { color: red; font-weight: bold; }
        .data-block { background: #f9f9f9; padding: 10px; border-left: 4px solid #007bff; margin: 10px 0; }
    </style>
</head>
<body>

    <div class="card">
        <!-- Внедрение PHP в HTML с помощью сокращенного тега <?= round($result, 2) ?> -->
        <h1><?= UNIVERSITY ?></h1>
        <h2><?= DISCIPLINE ?></h2>
        <p><strong>Студент:</strong> <?= $studentName ?></p>
        <p><strong>Группа:</strong> <?= $group ?>; <strong>Курс:</strong> <?= $course ?>; <strong>Вариант:</strong> <?= $variant ?></p>
        
        <div class="data-block">
            <h3>Исходные данные (Оценки):</h3>
            <ul>
                <li>Оценка 1: <?= $grade1 ?></li>
                <li>Оценка 2: <?= $grade2 ?></li>
                <li>Оценка 3: <?= $grade3 ?></li>
            </ul>
        </div>

        <div class="data-block">
            <h3>Результаты вычислений:</h3>
            <!-- Округление результата до 2 знаков после запятой -->
            <p><strong>Средний балл:</strong> <?= round($result, 2) ?></p> 
            <p><strong>Статус:</strong> <span class="<?= $statusClass ?>"><?= $status ?></span></p>
        </div>
        
        <p style="margin-top: 20px; font-size: 0.9em; color: #666;">
            <strong>Дата формирования:</strong> <?= date("d.m.Y") ?>
        </p>
    </div>

</body>
</html>