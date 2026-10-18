<?php

declare(strict_types=1);

/** @var string $title */
/** @var array{type:string,message:string}|null $flash */
/** @var string $templatePath */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="logo" href="/">Консультации</a>
    <nav>
        <a href="/">Слоты</a>
        <a href="/?route=/my">Мои записи</a>
    </nav>
</header>

<?php if ($flash !== null): ?>
    <div class="flash flash--<?= htmlspecialchars($flash['type']) ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<main class="container">
    <?php require $templatePath; ?>
</main>

<footer class="site-footer">
    Учебный проект · Тема 19 · PHP 8.4 · 2026
</footer>
</body>
</html>