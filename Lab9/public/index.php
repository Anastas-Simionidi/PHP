<?php
declare(strict_types=1);

// Стартуем сессию, чтобы сохранять список бронирований между отправками формы
session_start();

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Domain\{Student, Coach, Training, TrainingCode};

// Инициализируем базовые объекты (тренер и тренировка)
$coach = new Coach(1, 'Дмитрий Петров', 'dima@sport.kz', 'Бокс');
$code = new TrainingCode('BOX-101');

// Вместимость нашей интерактивной тренировки сделаем маленькой (например, 3 места) для удобства тестов
$capacity = 3;
$training = new Training($code, 'Вечерний бокс', $coach, new \DateTimeImmutable('2026-11-05 18:00:00'), $capacity);

// Восстанавливаем ранее сохраненные бронирования из сессии
if (isset($_SESSION['saved_bookings']) && is_array($_SESSION['saved_bookings'])) {
    foreach ($_SESSION['saved_bookings'] as $studentData) {
        try {
            $existingStudent = new Student($studentData['id'], $studentData['name'], $studentData['email'], $studentData['membership']);
            $training->book($existingStudent);
        } catch (\Throwable $e) {
            // Игнорируем ошибки при восстановлении
        }
    }
}

// Сообщения для вывода на экран
$successMessage = '';
$errorMessage = '';

// Обработка отправки формы (Клик по кнопке "Записать студента")
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Кнопка Сбросить данные
    if (isset($_POST['action_reset'])) {
        $_SESSION['saved_bookings'] = [];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Добавление студента
    if (isset($_POST['action_book'])) {
        try {
            $id = (int)($_POST['student_id'] ?? 0);
            $name = (string)($_POST['student_name'] ?? '');
            $email = (string)($_POST['student_email'] ?? '');
            $membership = (string)($_POST['student_membership'] ?? '');

            // Создаем объект студента (здесь могут вылететь ошибки InvalidArgumentException)
            $newStudent = new Student($id, $name, $email, $membership);

            // Пытаемся записать на тренировку (здесь могут вылететь ошибки DomainException)
            $training->book($newStudent);

            // Если всё успешно, сохраняем в сессию
            $_SESSION['saved_bookings'][] = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'membership' => $membership
            ];

            $successMessage = "Студент " . htmlspecialchars($newStudent->fullName()) . " успешно записан!";
        } catch (\Throwable $e) {
            // Перехватываем нарушения бизнес-правил
            $errorMessage = "Ошибка бизнес-логики: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интерактивный Спортивный Центр</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; margin: 40px; }
        .container { max-width: 600px; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; color: #007bff; }
        .info-block { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; color: white; float: right; }
        .btn-danger:hover { background: #bd2130; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        ul { padding-left: 20px; }
        li { margin-bottom: 5px; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h2>=== Панель управления тренировкой ===</h2>
    
    <div class="info-block">
        <b>Занятие:</b> <?= htmlspecialchars($training->title) ?><br>
        <b>Код занятия:</b> <?= $training->code->value ?><br>
        <b>Тренер:</b> <?= htmlspecialchars($training->coach->fullName()) ?> (<?= htmlspecialchars($training->coach->roleLabel()) ?>)<br>
        <b>Макс. вместимость:</b> <?= $capacity ?> мест(а)<br>
        <b>Свободных мест осталось:</b> <span style="font-size: 16px; color: red; font-weight: bold;"><?= $training->freePlaces() ?></span>
    </div>

    <!-- Вывод уведомлений -->
    <?php if ($successMessage !== ''): ?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>

    <!-- Форма отправки данных -->
    <form method="POST">
        <div class="form-group">
            <label for="student_id">ID Студента (Число > 0):</label>
            <input type="number" id="student_id" name="student_id" value="<?= rand(1, 100) ?>" required>
        </div>
        <div class="form-group">
            <label for="student_name">ФИО Студента (Минимум 3 символа):</label>
            <input type="text" id="student_name" name="student_name" placeholder="Например, Алия Нур" required>
        </div>
        <div class="form-group">
            <label for="student_email">Email:</label>
            <input type="text" id="student_email" name="student_email" placeholder="student@example.kz" required>
        </div>
        <div class="form-group">
            <label for="student_membership">Номер абонемента:</label>
            <input type="text" id="student_membership" name="student_membership" placeholder="ST-2026-X" required>
        </div>

        <button type="submit" name="action_book" class="btn btn-primary">Записать студента на занятие</button>
        <button type="submit" name="action_reset" class="btn btn-danger" formnovalidate>Сбросить данные</button>
    </form>

    <h3 style="margin-top: 30px;">Список текущих записей:</h3>
    <?php if (count($training->bookings()) === 0): ?>
        <p style="color: #666; font-style: italic;">На этот сеанс пока никто не записан.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($training->bookings() as $booking): ?>
                <?php 
                    $name = 'Студент #' . $booking->studentId;
                    if (isset($_SESSION['saved_bookings'])) {
                        foreach ($_SESSION['saved_bookings'] as $s) {
                            if ($s['id'] === $booking->studentId) { $name = $s['name']; break; }
                        }
                    }
                ?>
                <li>
                    <b>ID #<?= $booking->studentId ?></b> — <?= htmlspecialchars($name) ?> 
                    <small style="color: #666;">(Записан в: <?= $booking->bookedAt->format('H:i:s') ?>)</small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

</body>
</html>
