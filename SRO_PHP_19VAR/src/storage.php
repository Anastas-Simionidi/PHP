<?php

declare(strict_types=1);

function ensure_data_dir(): void
{
    if (!is_dir(DATA_DIR) && !mkdir(DATA_DIR, 0775, true) && !is_dir(DATA_DIR)) {
        throw new RuntimeException('Не удалось создать каталог данных.');
    }
}

/** @return array<int, mixed> */
function read_json_array(string $file): array
{
    if (!is_file($file)) {
        return [];
    }
    $raw = file_get_contents($file);
    if ($raw === false || $raw === '') {
        return [];
    }
    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        throw new RuntimeException('Повреждённый JSON: ' . basename($file), 0, $e);
    }
    return is_array($data) ? $data : [];
}

/** @param array<int, mixed> $data */
function write_json_atomic(string $file, array $data): void
{
    ensure_data_dir();
    $tmp  = $file . '.tmp.' . bin2hex(random_bytes(6));
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if (file_put_contents($tmp, $json, LOCK_EX) === false) {
        throw new RuntimeException('Не удалось записать временный файл.');
    }
    if (!rename($tmp, $file)) {
        @unlink($tmp);
        throw new RuntimeException('Не удалось атомарно заменить файл.');
    }
}

/**
 * Чтение-изменение-запись под эксклюзивной блокировкой.
 *
 * @param callable(array<int,mixed>):array<int,mixed> $mutator
 * @return array<int,mixed>
 */
function modify_json(string $file, callable $mutator): array
{
    ensure_data_dir();
    $lockFile = $file . '.lock';
    $fp = fopen($lockFile, 'c');
    if ($fp === false) {
        throw new RuntimeException('Не удалось открыть lock-файл.');
    }
    try {
        if (!flock($fp, LOCK_EX)) {
            throw new RuntimeException('Не удалось получить блокировку.');
        }
        $data    = read_json_array($file);
        $newData = $mutator($data);
        write_json_atomic($file, $newData);
        return $newData;
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}