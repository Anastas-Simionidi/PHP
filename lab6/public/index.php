<?php
declare(strict_types=1);

/**
 * Функция безопасного экранирования динамического HTML-вывода
 */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Функция безопасного извлечения строкового поля из $_POST
 */
function postString(string $key): ?string
{
    $value = $_POST[$key] ?? null;
    return is_string($value) ? $value : null;
}

// Списки разрешенных значений (Allow-lists)
$allowedGroups = ['ИС-23-21', 'ИС-23-22', 'ВТ-23-01', 'ВТ-23-02'];
$allowedDisciplines = ['Программирование на PHP', 'Базы данных', 'Веб-дизайн', 'Алгоритмы и структуры данных'];
$allowedFormats = ['offline', 'online'];

// Начальные значения полей формы
$values = [
    'full_name'  => '',
    'email'      => '',
    'group'      => '',
    'discipline' => '',
    'format'     => '',
];

$errors = [];
$success = false;

// Обработка формы при POST-запросе
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    
    // 1. Извлечение данных
    $fullName   = postString('full_name');
    $email      = postString('email');
    $group      = postString('group');
    $discipline = postString('discipline');
    $format     = postString('format');

    // 2. Нормализация данных
    $values['full_name']  = $fullName === null ? '' : trim($fullName);
    $values['email']      = $email === null ? '' : trim($email);
    $values['group']      = $group ?? '';
    $values['discipline'] = $discipline ?? '';
    $values['format']     = $format ?? '';

    // 3. Серверная валидация
    if ($fullName === null || $values['full_name'] === '') {
        $errors['full_name'] = 'Укажите ФИО.';
    } elseif (mb_strlen($values['full_name']) > 100) {
        $errors['full_name'] = 'ФИО не должно превышать 100 символов.';
    }

    if ($email === null || $values['email'] === '') {
        $errors['email'] = 'Укажите адрес электронной почты.';
    } elseif (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Введите корректный адрес электронной почты.';
    }

    if (!in_array($values['group'], $allowedGroups, true)) {
        $errors['group'] = 'Выберите учебную группу из списка.';
    }

    if (!in_array($values['discipline'], $allowedDisciplines, true)) {
        $errors['discipline'] = 'Выберите дисциплину из списка.';
    }

    if (!in_array($values['format'], $allowedFormats, true)) {
        $errors['format'] = 'Выберите формат участия.';
    }

    if (!isset($_POST['agreement']) || $_POST['agreement'] !== '1') {
        $errors['agreement'] = 'Необходимо подтвердить согласие с правилами регистрации.';
    }

    $success = ($errors === []);
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация на дисциплину</title>
    <style>
        body { font-family: sans-serif; margin: 40px; background: #f9f9f9; }
        main { max-width: 500px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        label, fieldset { display: block; margin-top: 15px; font-weight: bold; }
        input[type="text"], input[type="email"], select { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        fieldset { border: 1px solid #ccc; padding: 10px; border-radius: 4px; }
        fieldset label { font-weight: normal; margin-top: 5px; display: inline-block; margin-right: 15px; }
        .error { color: #d9534f; font-size: 0.9em; font-weight: normal; margin: 5px 0 0 0; }
        .success { color: #5cb85c; font-size: 1.2em; font-weight: bold; }
        button { margin-top: 20px; padding: 10px 20px; background: #0275d8; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #014c8c; }
        .checkbox-label { font-weight: normal; display: inline-block; margin-top: 15px; }
    </style>
</head>
<body>
<main>
    <h1>Регистрация на дисциплину</h1>

    <?php if ($success): ?>
        <p class="success">Заявка принята! Студент <strong><?= h($values['full_name']) ?></strong> успешно зарегистрирован на курс «<?= h($values['discipline']) ?>».</p>
        <p><a href="">Зарегистрировать еще одного студента</a></p>
    <?php else: ?>
        <form method="post" action="">
            
            <label>
                ФИО
                <input type="text" name="full_name" value="<?= h($values['full_name']) ?>" maxlength="100" required>
            </label>
            <?php if (isset($errors['full_name'])): ?>
                <p class="error"><?= h($errors['full_name']) ?></p>
            <?php endif; ?>

            <label>
                E-mail
                <input type="email" name="email" value="<?= h($values['email']) ?>" required>
            </label>
            <?php if (isset($errors['email'])): ?>
                <p class="error"><?= h($errors['email']) ?></p>
            <?php endif; ?>

            <label>
                Учебная группа
                <select name="group" required>
                    <option value="">-- Выберите группу --</option>
                    <?php foreach ($allowedGroups as $item): ?>
                        <option value="<?= h($item) ?>" <?= $values['group'] === $item ? 'selected' : '' ?>>
                            <?= h($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if (isset($errors['group'])): ?>
                <p class="error"><?= h($errors['group']) ?></p>
            <?php endif; ?>

            <label>
                Дисциплина
                <select name="discipline" required>
                    <option value="">-- Выберите дисциплину --</option>
                    <?php foreach ($allowedDisciplines as $item): ?>
                        <option value="<?= h($item) ?>" <?= $values['discipline'] === $item ? 'selected' : '' ?>>
                            <?= h($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if (isset($errors['discipline'])): ?>
                <p class="error"><?= h($errors['discipline']) ?></p>
            <?php endif; ?>

            <fieldset>
                <legend>Формат участия</legend>
                <?php foreach ($allowedFormats as $item): ?>
                    <label>
                        <input type="radio" name="format" value="<?= h($item) ?>" <?= $values['format'] === $item ? 'checked' : '' ?>>
                        <?= h($item) ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
            <?php if (isset($errors['format'])): ?>
                <p class="error"><?= h($errors['format']) ?></p>
            <?php endif; ?>

            <label class="checkbox-label">
                <input type="checkbox" name="agreement" value="1">
                Я согласен с правилами регистрации на дисциплину
            </label>
            <?php if (isset($errors['agreement'])): ?>
                <p class="error"><?= h($errors['agreement']) ?></p>
            <?php endif; ?>

            <button type="submit">Отправить заявку</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
