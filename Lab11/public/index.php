<?php
declare(strict_types=1);

// Включаем отображение ошибок
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';

use University\CourseCatalog\Model\Book;
use University\CourseCatalog\Model\Member;
use University\CourseCatalog\Repository\InMemoryBookRepository;
use University\CourseCatalog\Service\LoanService;
use University\CourseCatalog\Exception\InvalidLibraryDataException;

// Запускаем сессию, чтобы данные не стирались при перезагрузке страницы
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Инициализируем репозиторий в сессии для сохранения динамики
if (!isset($_SESSION['books'])) {
    $_SESSION['books'] = [
        '978-5-97-060237-9' => new Book('978-5-97-060237-9', 'PHP 8 на примерах', 'А. Кузнецов', 3),
        '978-5-93-286153-0' => new Book('978-5-93-286153-0', 'Совершенный код', 'С. Макконнелл', 1)
    ];
}

if (!isset($_SESSION['members'])) {
    $_SESSION['members'] = [
        'M-1001' => new Member('M-1001', 'Иван Иванов'),
        'M-1002' => new Member('M-1002', 'Анна Петрова')
    ];
}

// Настраиваем слои приложения
$repository = new InMemoryBookRepository();
foreach ($_SESSION['books'] as $book) {
    $repository->save($book);
}
$loanService = new LoanService($repository);
$members = &$_SESSION['members'];

$message = '';
$error = '';

// Обработка интерактивных действий (POST-запросы)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_book') {
            $isbn = trim((string)$_POST['isbn']);
            $title = trim((string)$_POST['title']);
            $author = trim((string)$_POST['author']);
            $copies = (int)$_POST['copies'];

            $newBook = new Book($isbn, $title, $author, $copies);
            $repository->save($newBook);
            $_SESSION['books'][$isbn] = $newBook;
            $message = "✅ Книга '{$title}' успешно добавлена в фонд!";
        } 
        
        elseif ($action === 'loan_book') {
            $mid = trim((string)$_POST['member_id']);
            $isbn = trim((string)$_POST['isbn']);

            if (!isset($members[$mid])) {
                throw new \LogicException("Читатель с ID $mid не найден.");
            }

            $loanService->loanBook($members[$mid], $isbn);
            // Синхронизируем изменения количества копий обратно в сессию
            $_SESSION['books'][$isbn] = $repository->findByIsbn($isbn);
            $message = "✅ Книга успешно выдана читателю {$members[$mid]->name}!";
        } 
        
        elseif ($action === 'return_book') {
            $mid = trim((string)$_POST['member_id']);
            $isbn = trim((string)$_POST['isbn']);

            if (!isset($members[$mid])) {
                throw new \LogicException("Читатель с ID $mid не найден.");
            }

            $loanService->returnBook($members[$mid], $isbn);
            $_SESSION['books'][$isbn] = $repository->findByIsbn($isbn);
            $message = "✅ Книга успешно возвращена в библиотеку!";
        }
    } catch (InvalidLibraryDataException $e) {
        $error = "❌ Ошибка валидации данных: " . $e->getMessage();
    } catch (\LogicException $e) {
        $error = "❌ Отказ в операции: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интерактивная Библиотека (PSR-4)</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 8px; }
        h1, h2 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 8px; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .card { background: #fff; border: 1px solid #e2e8f0; padding: 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; color: #64748b; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; color: #475569; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; }
        button { background: #3b82f6; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        button:hover { background: #2563eb; }
        .btn-return { background: #ef4444; padding: 4px 8px; font-size: 12px; display: inline-block; width: auto; color: white;}
        .btn-return:hover { background: #dc2626; }
        ul { padding-left: 20px; margin: 5px 0; }
    </style>
</head>
<body>

<div class="container">
    <h1>📚 Учебный проект: Интерактивная библиотека</h1>
    <p style="color: #64748b;">Лабораторная работа 11 • Вариант №1 • Автозагрузка PSR-4</p>

    <!-- Уведомления об операциях -->
    <?php if ($message): ?>
        <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="grid">
        <!-- ТАБЛИЦА КНИГ -->
        <div class="card" style="grid-column: span 2;">
            <h2>📖 Книжный фонд библиотеки (InMemoryRepository)</h2>
            <table>
                <thead>
                    <tr>
                        <th>ISBN (Уникальный код)</th>
                        <th>Название книги</th>
                        <th>Автор</th>
                        <th>Доступно копий</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($repository->all() as $b): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($b->isbn) ?></code></td>
                            <td><strong><?= htmlspecialchars($b->title) ?></strong></td>
                            <td><?= htmlspecialchars($b->author) ?></td>
                            <td>
                                <span style="background: <?= $b->copies > 0 ? '#dcfce7' : '#fee2e2' ?>; color: <?= $b->copies > 0 ? '#166534' : '#991b1b' ?>; padding: 3px 8px; border-radius: 12px; font-size: 13px; font-weight: bold;">
                                    <?= $b->copies ?> шт.
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ФОРМА ДОБАВЛЕНИЯ КНИГИ -->
        <div class="card">
            <h2>➕ Добавить новую книгу</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_book">
                <div class="form-group">
                    <label>ISBN (Шаблон: 123-4-56-789012-3)</label>
                    <input type="text" name="isbn" placeholder="978-5-00-000000-0" required>
                </div>
                <div class="form-group">
                    <label>Название книги</label>
                    <input type="text" name="title" placeholder="например, PHP: Правильный путь" required>
                </div>
                <div class="form-group">
                    <label>Автор</label>
                    <input type="text" name="author" placeholder="Имя Автора" required>
                </div>
                <div class="form-group">
                    <label>Количество экземпляров</label>
                    <input type="number" name="copies" value="1" min="1" max="10" required>
                </div>
                <button type="submit">Добавить на полку</button>
            </form>
        </div>

        <!-- ФОРМА ВЫДАЧИ КНИГИ ЧИТАТЕЛЮ -->
        <div class="card">
            <h2>🤝 Выдать книгу читателю (LoanService)</h2>
            <form method="POST">
                <input type="hidden" name="action" value="loan_book">
                <div class="form-group">
                    <label>Выберите читателя</label>
                    <select name="member_id" required>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m->memberId ?>"><?= htmlspecialchars($m->name) ?> (<?= $m->memberId ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Выберите книгу для выдачи</label>
                    <select name="isbn" required>
                        <?php foreach ($repository->all() as $b): ?>
                            <option value="<?= $b->isbn ?>" <?= $b->copies === 0 ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($b->title) ?> (Осталось: <?= $b->copies ?> шт.)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" style="background: #10b981;">Оформить выдачу</button>
            </form>
        </div>

        <!-- СПИСОК ЧИТАТЕЛЕЙ И ИХ КНИГ -->
        <div class="card" style="grid-column: span 2;">
            <h2>👥 Читательские билеты и книги на руках</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ФИО Читателя</th>
