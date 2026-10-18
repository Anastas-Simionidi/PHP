<?php

declare(strict_types=1);

/** @var array<string,mixed> $slot */
/** @var array<string,string> $errors */
/** @var array<string,string> $values */
?>
<h1>Запись на консультацию</h1>

<div class="slot-card">
    <p><strong>Преподаватель:</strong> <?= htmlspecialchars((string) $slot['teacher']) ?></p>
    <p><strong>Дата и время:</strong>
        <?= htmlspecialchars((string) $slot['date'] . ' ' . (string) $slot['time']) ?></p>
    <p><strong>Аудитория:</strong> <?= htmlspecialchars((string) $slot['room']) ?></p>
</div>

<form method="post" action="/?route=/book" class="form" novalidate>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <input type="hidden" name="slot_id" value="<?= htmlspecialchars($values['slot_id']) ?>">

    <label>
        Ваше имя
        <input type="text" name="student_name" maxlength="120" required
               value="<?= htmlspecialchars($values['student_name']) ?>">
        <?php if (isset($errors['student_name'])): ?>
            <span class="error"><?= htmlspecialchars($errors['student_name']) ?></span>
        <?php endif; ?>
    </label>

    <label>
        Email
        <input type="email" name="email" maxlength="254" required
               value="<?= htmlspecialchars($values['email']) ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="error"><?= htmlspecialchars($errors['email']) ?></span>
        <?php endif; ?>
    </label>

    <label>
        Комментарий (необязательно, до 500 символов)
        <textarea name="comment" rows="4" maxlength="500"><?= htmlspecialchars($values['comment']) ?></textarea>
        <?php if (isset($errors['comment'])): ?>
            <span class="error"><?= htmlspecialchars($errors['comment']) ?></span>
        <?php endif; ?>
    </label>

    <button type="submit" class="btn">Подтвердить запись</button>
</form>