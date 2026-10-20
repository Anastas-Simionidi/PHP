<h1><?= $user['role'] === 'teacher' ? 'Записи ко мне' : 'Мои записи' ?></h1>
<?php if (empty($bookings)): ?><p class="empty">Записей нет.</p><?php else: ?>
    <table class="slots">
        <thead><tr><th>Дата / Время</th><?php if ($user['role'] === 'teacher'): ?><th>Студент</th><?php endif; ?><th>Аудитория</th><th>Статус</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['slot']['date'] . ' ' . $b['slot']['time']) ?></td>
                <?php if ($user['role'] === 'teacher'): ?><td><?= htmlspecialchars($b['student_name']) ?></td><?php endif; ?>
                <td><?= htmlspecialchars($b['room_name'] ?? '?') ?></td>
                <td><span class="badge badge--<?= $b['status'] === 'active' ? 'free' : 'busy' ?>"><?= $b['status'] === 'active' ? 'Активна' : 'Отменена' ?></span></td>
                <td>
                    <?php if ($user['role'] === 'student' && $b['status'] === 'active'): ?>
                        <form method="post" action="index.php?route=/cancel" onsubmit="return confirm('Отменить?');">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($b['id']) ?>">
                            <button type="submit" class="btn btn--danger">Отменить</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>