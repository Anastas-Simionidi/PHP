<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

const DATA_DIR              = __DIR__ . '/../var/data';
const SLOTS_FILE            = DATA_DIR . '/slots.json';
const BOOKINGS_FILE         = DATA_DIR . '/bookings.json';
const CANCEL_MINUTES_BEFORE = 60;
const TZ_NAME               = 'Asia/Almaty';

require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/bookings.php';

set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);
    http_response_code(500);
    echo 'Внутренняя ошибка сервера';
});