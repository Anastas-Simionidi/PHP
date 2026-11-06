<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$storedPath = null;
try {
    $file = uploadedFile('document');
    $mime = pdfMime($file['tmp_name']);
    
    // Индивидуальное требование: принимаем и чистим поле дисциплины
    $discipline = trim($_POST['discipline'] ?? '');
    if ($discipline === '') {
        throw new RuntimeException('Поле "Дисциплина" обязательно для заполнения.');
    }

    // Безопасное непредсказуемое имя для хранения
    $id = bin2hex(random_bytes(16));
    $storedName = $id . '.pdf';
    $storedPath = UPLOAD_DIR . '/' . $storedName;

    if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
        throw new RuntimeException('Не удалось перенести файл в хранилище.');
    }

    // Записываем данные (включая наше кастомное поле 'discipline')
    appendMetadata([
        'id' => $id,
        'original_name' => basename((string) $file['name']),
        'stored_name' => $storedName,
        'mime' => $mime,
        'size' => filesize($storedPath),
        'sha256' => hash_file('sha256', $storedPath),
        'uploaded_at' => (new DateTimeImmutable())->format(DATE_ATOM),
        'discipline' => $discipline // <-- Сохранение требования варианта 1
    ]);

    header('Location: /index.php?ok=1');
    exit;
} catch (Throwable $e) {
    // Откат: если упали на записи в JSON, удаляем уже загруженный физический файл
    if ($storedPath !== null && is_file($storedPath)) {
        unlink($storedPath);
    }
    error_log($e->__toString());
    header('Location: /index.php?error=upload');
    exit;
}
