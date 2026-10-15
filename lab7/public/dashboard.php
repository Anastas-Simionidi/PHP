<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/session.php';
requireLogin();

$lang = getLang();
$role = $_SESSION['role'] ?? '';
$email = $_SESSION['email'] ?? '';

// Имитация данных заявок
$applications = [
    ['id' => 1, 'student_email' => 'student@atu.edu.kz', 'course' => 'Программирование на PHP', 'status' => 'ожидает'],
    ['id' => 2, 'student_email' => 'student@atu.edu.kz', 'course' => 'Базы данных', 'status' => 'подтверждена'],
    ['id' => 3, 'student_email' => 'other@atu.edu.kz', 'course' => 'Веб-разработка', 'status' => 'ожидает'],
];

$message = '';

// Обработка POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    // Смена языка
    if (isset($_POST['lang'])) {
        $allowedLangs = ['ru', 'en', 'kz'];
        $newLang = $_POST['lang'];
        if (is_string($newLang) && in_array($newLang, $allowedLangs, true)) {
            setcookie('lang', $newLang, [
                'expires' => time() + 2592000,
                'path' => '/',
                'secure' => $isHTTPS,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            header('Location: /dashboard.php');
            exit;
        }
    }

    // Подтверждение заявки (только регистратор)
    if ($role === 'registrar' && isset($_POST['application_id'])) {
        $appId = (int)$_POST['application_id'];
        // В реальной системе здесь было бы обновление в БД
        $message = "Заявка #$appId подтверждена.";
    }
}

// Тексты в зависимости от языка
$texts = [
    'ru' => [
        'title' => 'Личный кабинет',
        'welcome' => 'Добро пожаловать',
        'role' => 'Роль',
        'lang' => 'Язык интерфейса',
        'logout' => 'Выйти',
        'applications' => 'Заявки',
        'course' => 'Дисциплина',
        'status' => 'Статус',
        'actions' => 'Действия',
        'confirm' => 'Подтвердить',
    ],
    'en' => [
        'title' => 'Dashboard',
        'welcome' => 'Welcome',
        'role' => 'Role',
        'lang' => 'Interface language',
        'logout' => 'Logout',
        'applications' => 'Applications',
        'course' => 'Course',
        'status' => 'Status',
        'actions' => 'Actions',
        'confirm' => 'Confirm',
    ],
    'kz' => [
        'title' => 'Жеке кабинет',
        'welcome' => 'Қош келдіңіз',
        'role' => 'Рөл',
        'lang' => 'Интерфейс тілі',
        'logout' => 'Шығу',
        'applications' => 'Өтінімдер',
        'course' => 'Пән',
        'status' => 'Мәртебе',
        'actions' => 'Әрекеттер',
        'confirm' => 'Растау',
    ],
];
$t = $texts[$lang] ?? $texts['ru'];
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= h($t['title']) ?></title>
</head>
<body>
    <h1><?= h($t['title']) ?></h1>
    <p><?= h($t['welcome']) ?>, <?= h($email) ?>!</p>
    <p><?= h($t['role']) ?>: <?= h($role) ?></p>

    <?php if ($message !== ''): ?>
        <p style="color:green;"><?= h($message) ?></p>
    <?php endif; ?>

    <!-- Форма смены языка -->
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <label><?= h($t['lang']) ?>:
            <select name="lang">
                <option value="ru" <?= $lang === 'ru' ? 'selected' : '' ?>>Русский</option>
                <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>English</option>
                <option value="kz" <?= $lang === 'kz' ? 'selected' : '' ?>>Қазақша</option>
            </select>
        </label>
        <button type="submit">OK</button>
    </form>

    <h2><?= h($t['applications']) ?></h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th><?= h($t['course']) ?></th>
            <th><?= h($t['status']) ?></th>
            <?php if ($role === 'registrar'): ?>
                <th><?= h($t['actions']) ?></th>
            <?php endif; ?>
        </tr>
        <?php foreach ($applications as $app): ?>
            <?php if ($role === 'student' && $app['student_email'] !== $email) continue; ?>
            <tr>
                <td><?= h((string)$app['id']) ?></td>
                <td><?= h($app['course']) ?></td>
                <td><?= h($app['status']) ?></td>
                <?php if ($role === 'registrar'): ?>
                    <td>
                        <?php if ($app['status'] === 'ожидает'): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="application_id" value="<?= h((string)$app['id']) ?>">
                                <button type="submit"><?= h($t['confirm']) ?></button>
                            </form>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <hr>
    <form method="post" action="/logout.php">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <button type="submit"><?= h($t['logout']) ?></button>
    </form>
</body>
</html>