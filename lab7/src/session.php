<?php
declare(strict_types=1);

// Настройки сессии до session_start()
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

$isHTTPS = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

session_name('UNISESSION');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHTTPS,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

const IDLE_TIMEOUT = 900; // 15 минут

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function loginUser(int $id, string $email, string $role): void
{
    session_regenerate_id(true);
    unset($_SESSION['login_csrf']);
    $_SESSION['user_id'] = $id;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['last_activity'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

function requireLogin(): void
{
    $userId = $_SESSION['user_id'] ?? null;
    $lastActivity = $_SESSION['last_activity'] ?? null;

    if (!is_int($userId) || !is_int($lastActivity)) {
        header('Location: /login.php');
        exit;
    }

    if (time() - $lastActivity > IDLE_TIMEOUT) {
        logoutUser();
        header('Location: /login.php?expired=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

function requireRole(string $role): void
{
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Доступ запрещён');
    }
}

function getLang(): string
{
    $allowed = ['ru', 'en', 'kz'];
    $lang = $_COOKIE['lang'] ?? 'ru';
    if (!is_string($lang) || !in_array($lang, $allowed, true)) {
        $lang = 'ru';
    }
    return $lang;
}