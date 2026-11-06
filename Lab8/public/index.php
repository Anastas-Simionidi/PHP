<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/functions.php';

try { 
    $items = loadMetadata(); 
    
    // Индивидуальное требование: сортировка массива по дате (uploaded_at) от новых к старым
    usort($items, function(array $a, array $b): int {
        return strcmp($b['uploaded_at'] ?? '', $a['uploaded_at'] ?? '');
    });
} catch (Throwable $e) { 
    $items = []; 
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Реестр лабораторных работ</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; background: #f4f6f9; color: #333; }
        h1, h2 { color: #2c3e50; }
        form { background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 30px; }
        label { display: block; margin-bottom: 15px; font-weight: bold; }
        input[type="text"], input[type="file"] { display: block; width: 100%; margin-top: 5px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #2980b9; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; }
        tr:hover { background: #f9f9f9; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #c0392b; font-weight: bold; }
        .download-btn { color: #3498db; text-decoration: none; font-weight: bold; }
        .download-btn:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Реестр лабораторных работ (Вариант 1)</h1>
    
    <?php if (isset($_GET['ok'])): ?>
        <p class="success">✓ Файл успешно принят и внесён в реестр.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="error">❌ Ошибка загрузки. Убедитесь, что передаёте PDF до 5 МиБ.</p>
    <?php endif; ?>
    
    <!-- Форма отправки с enctype для файлов -->
    <form method="post" action="/upload.php" enctype="multipart/form-data">
        <label>
            Дисциплина:
            <input type="text" name="discipline" placeholder="Например: Программирование на PHP" required>
        </label>
        
        <label>
            Файл отчёта (только PDF, макс. 5 МБ):
            <input type="file" name="document" accept="application/pdf" required>
        </label>
        
        <button type="submit">Загрузить отчёт</button>
    </form>

    <h2>Список загруженных отчётов</h2>
    <table>
        <thead>
            <tr>
                <th>Дисциплина</th>
                <th>Оригинальное имя</th>
                <th>Размер (байт)</th>
                <th>Дата загрузки (ISO)</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="5" style="text-align: center; color: #7f8c8d;">Реестр пока пуст</td></tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                <tr>
                    <!-- Все данные строго экранируем через h() для защиты от XSS -->
                    <td><?= h((string)($item['discipline'] ?? 'Не указана')) ?></td>
                    <td><?= h((string)$item['original_name']) ?></td>
                    <td><?= h((string)$item['size']) ?></td>
                    <td><?= h((string)$item['uploaded_at']) ?></td>
                    <td>
                        <a class="download-btn" href="/download.php?id=<?= h((string)$item['id']) ?>">Скачать</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
