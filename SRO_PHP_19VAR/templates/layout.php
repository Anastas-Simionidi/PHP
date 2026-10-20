<?php declare(strict_types=1); $user = current_user(); $flash = flash_take(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ?? 'Консультации') ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="logo" href="index.php">Консультации</a>
    <nav>
        <a href="index.php">Слоты</a>
        <?php if ($user): ?>
            <a href="index.php?route=/my">Записи</a>
            <?php if ($user['role'] === 'teacher'): ?><a href="index.php?route=/teacher" style="color:blue;">Панель препода</a><?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?><a href="index.php?route=/admin" style="color:red;">Админка</a><?php endif; ?>
            <span class="user-info"><?= htmlspecialchars($user['name']) ?></span>
            <a href="index.php?route=/logout" class="logout">Выйти</a>
        <?php else: ?>
            <a href="index.php?route=/login">Войти</a>
        <?php endif; ?>
    </nav>
</header>
<?php if ($flash): ?>
    <div class="flash flash--<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>
<main class="container"><?php require $templatePath; ?></main>
</body>
</html>