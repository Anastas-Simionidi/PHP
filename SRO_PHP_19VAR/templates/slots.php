<h1>Свободные слоты</h1>
<?php if (empty($slots)): ?><p class="empty">Нет слотов.</p><?php else: ?>
    <table class="slots">
        <thead><tr><th>Дата</th><th>Время</th><th>Преподаватель</th><th>Записано</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($slots as $row): $slot = $row['slot']; ?>
            <tr>
                <td><?= htmlspecialchars($slot['date']) ?></td>
                <td><?= htmlspecialchars($slot['time']) ?></td>
                <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                <td>
                    <span class="badge badge--<?= $row['is_full'] ? 'busy' : 'free' ?>">
                        <?= $row['bookings_count'] ?> / <?= MAX_STUDENTS ?> мест
                    </span>
                </td>
                <td>
                    <?php if (!$row['is_full'] && $user && $user['role'] === 'student'): ?>
                        <a class="btn" href="index.php?route=/book&slot_id=<?= urlencode($slot['id']) ?>">Записаться</a>
                    <?php elseif ($row['is_full']): ?>
                        <span class="hint">Мест нет</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>