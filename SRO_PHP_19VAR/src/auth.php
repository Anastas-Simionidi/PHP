<?php
declare(strict_types=1);

function login(string $email, string $password): bool {
    $users = read_json_array(USERS_FILE);
    foreach ($users as $u) {
        if (mb_strtolower($u['email']) === mb_strtolower($email) && password_verify($password, $u['password'])) {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['role'] = $u['role'];
            $_SESSION['name'] = $u['name'];
            session_regenerate_id(true);
            return true;
        }
    }
    return false;
}

function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    return ['id' => $_SESSION['user_id'], 'role' => $_SESSION['role'], 'name' => $_SESSION['name']];
}

function require_role(string $role): void {
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        flash_set('error', 'Доступ запрещен.');
        header('Location: index.php?route=/login'); exit;
    }
}

function register_user(string $name, string $email, string $password, string $role = 'student'): void {
    $users = read_json_array(USERS_FILE);
    foreach ($users as $u) {
        if (mb_strtolower($u['email']) === mb_strtolower($email)) throw new DomainException('Email уже занят.');
    }
    if (mb_strlen($password) < 6) throw new DomainException('Пароль менее 6 символов.');
    if (!in_array($role, ['student', 'teacher'], true)) throw new DomainException('Некорректная роль.');

    $newUser = [
        'id' => 'u_' . bin2hex(random_bytes(4)),
        'role' => $role,
        'name' => $name,
        'email' => mb_strtolower($email),
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ];
    modify_json(USERS_FILE, static fn(array $users): array => array_merge($users, [$newUser]));
}