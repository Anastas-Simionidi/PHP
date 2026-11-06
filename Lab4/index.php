<?php
declare(strict_types=1);
session_start();

require_once 'functions.php';

// Инициализация дефолтного динамического массива в сессии, если пуст
if (!isset($_SESSION['patients_list'])) {
    $_SESSION['patients_list'] = [
        ['name' => 'Алексей', 'weight' => 85.0, 'height' => 1.80, 'gender' => 'м'],
        ['name' => 'Мария',   'weight' => 52.0, 'height' => 1.65, 'gender' => 'ж'],
        ['name' => 'Дмитрий', 'weight' => 110.0, 'height' => 1.75, 'gender' => 'м'],
    ];
}

// Переменные для интерактивного калькулятора формы одиночного расчета
$singleResult = null;
$errorMsg = null;

// Замыкание для коррекции веса (use передает величину поправки на одежду)
$clothingFix = 1.2; 
$computeNettoBMI = function (float $w, float $h) use ($clothingFix): float {
    return computeBMI($w - $clothingFix, $h, 1);
};

// 1. Обработка POST-запроса (Интерактивное добавление или быстрый расчет)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    $name = trim($_POST['name'] ?? '');
    $weight = (float)($_POST['weight'] ?? 0);
    $height = (float)($_POST['height'] ?? 0);
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;

    try {
        if ($action === 'calculate' || $action === 'add') {
            // Рассчитываем параметры
            $bmi = $computeNettoBMI($weight, $height);
            $category = getBMICategory($bmi);
            $recommendation = getHealthRecommendations($category, $gender);
            $report = formatBMIReport($name, $bmi, $category, $recommendation);
            
            $singleResult = [
                'bmi' => $bmi,
                'category' => $category,
                'recommendation' => $recommendation,
                'report' => $report
            ];

            if ($action === 'add' && !empty($name)) {
                // Динамическое добавление в общий список
                $_SESSION['patients_list'][] = [
                    'name' => $name, 'weight' => $weight, 'height' => $height, 'gender' => $gender
                ];
                // Сброс, чтобы очистить форму одиночного вывода после добавления
                $singleResult = null; 
            }
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// 2. Обработка GET-запроса на очистку списка
if (isset($_GET['clear'])) {
    $_SESSION['patients_list'] = [];
    header('Location: index.php');
    exit;
}

// 3. Callback-обработка всего динамического массива (Стрелочная функция)
$processedTable = array_map(function (array $p) use ($computeNettoBMI) {
    try {
        $bmi = $computeNettoBMI((float)$p['weight'], (float)$p['height']);
        $cat = getBMICategory($bmi);
        return [
            'name' => $p['name'], 'weight' => $p['weight'], 'height' => $p['height'],
            'bmi' => $bmi, 'category' => $cat, 'error' => null
        ];
    } catch (Exception $e) {
        return ['name' => $p['name'], 'error' => $e->getMessage()];
    }
}, $_SESSION['patients_list']);

// 4. Расчет агрегированной динамической статистики (Доп. задание)
$stats = ['min' => 0, 'max' => 0, 'avg' => 0];
$validRecords = array_filter($processedTable, fn($item) => $item['error'] === null);
if (!empty($validRecords)) {
    $stats = summarize($validRecords, fn($item) => $item['bmi']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интерактивный ИМТ Трекер</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 30px; background: #f4f7f6; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .full-width { grid-column: 1 / span 2; }
        h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 8px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { display: inline-block; padding: 10px 15px; color: white; background: #3498db; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-green { background: #2ecc71; }
        .btn-red { background: #e74c3c; float: right; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; }
        .alert-danger { background: #fde8e7; color: #c0392b; border-left: 5px solid #e74c3c; }
        .alert-success { background: #e8f8f5; color: #27ae60; border-left: 5px solid #2ecc71; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #2c3e50; color: white; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px; text-align: center; }
        .stat-item { background: #eef2f3; padding: 10px; border-radius: 4px; font-size: 1.1em; }
    </style>
</head>
<body>

<div class="container">
    <div class="card full-width">
        <h1>Лабораторная работа №4: Динамический ИМТ Менеджер</h1>
        <p>Параметры работы: Слой вычислений полностью изолирован. Активна директива <code>strict_types=1</code>. Применяется поправка на одежду: <strong>-<?= $clothingFix ?> кг</strong>.</p>
    </div>

    <!-- Интерактивная форма ввода -->
    <div class="card">
        <h2>Ввод данных пациента</h2>
        
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="form-group">
                <label>Имя пациента:</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Вес (кг):</label>
                <input type="number" step="0.1" name="weight" required value="<?= htmlspecialchars($_POST['weight'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Рост (метры, например 1.75):</label>
                <input type="number" step="0.01" name="height" required value="<?= htmlspecialchars($_POST['height'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Пол (для индивидуализации рекомендаций):</label>
                <select name="gender">
                    <option value="">Не указывать (Универсальный)</option>
                    <option value="м" <?= ($_POST['gender'] ?? '') === 'м' ? 'selected' : '' ?>>Мужской (м)</option>
                    <option value="ж" <?= ($_POST['gender'] ?? '') === 'ж' ? 'selected' : '' ?>>Женский (ж)</option>
                </select>
            </div>
            
            <button type="submit" name="action" value="calculate" class="btn">Просто рассчитать</button>
            <button type="submit" name="action" value="add" class="btn btn-green">Добавить в базу</button>
        </form>
    </div>

    <!-- Моментальный результат вычисления формы -->
    <div class="card">
        <h2>Экран быстрого ответа</h2>
        <?php if ($singleResult): ?>
            <div class="alert alert-success">
                <strong>Расчет завершен!</strong><br>
                ИМТ: <?= $singleResult['bmi'] ?><br>
                Категория: <?= $singleResult['category'] ?>
            </div>
            <p><strong>Сгенерированный текстовый отчет (Контракт):</strong></p>
            <textarea style="width:100%; height:80px; padding:5px;" readonly><?= htmlspecialchars($singleResult['report']) ?></textarea>
        <?php else: ?>
            <p style="color: #7f8c8d; font-style: italic; margin-top: 40px; text-align: center;">Введите корректные физические параметры слева и нажмите кнопку расчета.</p>
        <?php endif; ?>
    </div>

    <!-- Динамический список и статистика -->
    <div class="card full-width">
        <h2>База данных группы в текущей сессии 
            <a href="index.php?clear=1" class="btn btn-red" style="font-size: 0.8em; padding: 5px 10px;">Очистить базу</a>
        </h2>
        
        <table>
            <thead>
                <tr>
                    <th>ФИО пациента</th>
                    <th>Входной вес (кг)</th>
                    <th>Рост (м)</th>
                    <th>Чистый ИМТ</th>
                    <th>Статус здоровья</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($processedTable)): ?>
                    <tr><td colspan="5" style="text-align:center; color:#999;">Список пациентов пуст. Добавьте записи через форму.</td></tr>
                <?php else: ?>
                    <?php foreach ($processedTable as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <?php if ($row['error'] === null): ?>
                                <td><?= $row['weight'] ?></td>
                                <td><?= $row['height'] ?></td>
                                <td><strong><?= $row['bmi'] ?></strong></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                            <?php else: ?>
                                <td colspan="4" style="color:red;">Ошибка обработки: <?= htmlspecialchars($row['error']) ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 style="margin-top:20px;">Групповые метрики (Вызов функции summarize):</h3>
        <div class="stats-grid">
