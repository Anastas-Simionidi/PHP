<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/session.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';
$email = '';

$_SESSION['login_csrf'] ??= bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailValue = $_POST['email'] ?? null;
    $passwordValue = $_POST['password'] ?? null;
    $tokenValue = $_POST['csrf_token'] ?? null;

    if (
        is_string($emailValue) &&
        is_string($passwordValue) &&
        is_string($tokenValue) &&
        hash_equals($_SESSION['login_csrf'], $tokenValue)
    ) {
        $email = strtolower(trim($emailValue));
        // Учебные учетные данные
        $users = [
            'student@atu.edu.kz' => ['id' => 101, 'password' => 'Student123!', 'role' => 'student'],
            'registrar@atu.edu.kz' => ['id' => 102, 'password' => 'Registrar123!', 'role' => 'registrar'],
        ];

        if (isset($users[$email]) && $users[$email]['password'] === $passwordValue) {
            loginUser($users[$email]['id'], $email, $users[$email]['role']);
            header('Location: /dashboard.php');
            exit;
        }
    }
    $error = 'Неверный e-mail или пароль.';
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
</head>
<body>
    <h1>Регистрация на дисциплины</h1>
    <?php if (isset($_GET['expired'])): ?>
        <p>Сеанс завершён из-за бездействия.</p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p style="color:red;"><?= h($error) ?></p>
    <?php endif; ?>
    <form method="post" action="/login.php">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['login_csrf']) ?>">
        <label>E-mail
            <input name="email" type="email" required value="<?= h($email) ?>">
        </label><br>
        <label>Пароль
            <input name="password" type="password" required>
        </label><br>
        <button type="submit">Войти</button>
    </form>
    <p><small>Демо-доступ: student@atu.edu.kz / Student123! (студент), registrar@atu.edu.kz / Registrar123! (регистратор)</small></p>
</body>
</html>