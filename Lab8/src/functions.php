<?php
declare(strict_types=1);

const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 МиБ
const DATA_FILE = __DIR__ . '/../var/data/submissions.json';
const UPLOAD_DIR = __DIR__ . '/../var/uploads';

// Безопасное экранирование данных перед выводом в HTML
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Первичная проверка загружаемого файла
function uploadedFile(string $field): array {
    $file = $_FILES[$field] ?? null;
    if (!is_array($file) || is_array($file['error'] ?? null)) {
        throw new RuntimeException('Некорректное поле файла.');
    }
    // Проверяем ошибки UPLOAD_ERR_* в первую очередь
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Файл не загружен. Код ошибки: ' . $file['error']);
    }
    // Проверяем размер
    if (!is_int($file['size']) || $file['size'] < 1 || $file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('Недопустимый размер файла.');
    }
    // Проверка, что файл действительно загружен через HTTP POST
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Источник не является HTTP-загрузкой.');
    }
    return $file;
}

// Проверка реального MIME-типа через Fileinfo
function pdfMime(string $path): string {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($path);
    if ($mime !== 'application/pdf') {
        throw new RuntimeException('Разрешён загружать только файлы формата PDF.');
    }
    return $mime;
}

// Атомарное обновление реестра с блокировкой flock
function appendMetadata(array $item): void {
    $lockPath = DATA_FILE . '.lock';
    $lock = fopen($lockPath, 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        throw new RuntimeException('Не удалось заблокировать реестр.');
    }
    try {
        $json = is_file(DATA_FILE) ? file_get_contents(DATA_FILE) : '[]';
        if ($json === false) {
            throw new RuntimeException('Не удалось прочитать реестр.');
        }
        $items = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($items)) {
            throw new RuntimeException('Неверная структура реестра.');
        }
        
        $items[] = $item;
        
        $newJson = json_encode(
            $items,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
        
        $tmp = DATA_FILE . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (file_put_contents($tmp, $newJson, LOCK_EX) === false || !rename($tmp, DATA_FILE)) {
            @unlink($tmp);
            throw new RuntimeException('Не удалось обновить реестр.');
        }
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

// Чтение метаданных из реестра
function loadMetadata(): array {
    if (!is_file(DATA_FILE)) return [];
    $json = file_get_contents(DATA_FILE);
    if ($json === false) return [];
    $items = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    return is_array($items) ? $items : [];
}
