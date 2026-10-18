<?php

declare(strict_types=1);

/** @var array<int,array{slot:array<string,mixed>,start:DateTimeImmutable,is_booked:bool}> $slots */
?>
<h1>Свободные слоты на консультацию</h1>
<p class="hint">Прошедшие интервалы и занятые слоты отмечены автоматически.</p>

<?php if ($slots === []): ?>
    <p class="empty">Пока нет предстоящих слотов. Загляните позже.</p>
<?php else: ?>
    <table class="slots">
        <thead>
            <tr>
                <th>Дата</th>
                <th>Время</th>
                <th>Преподаватель</th>
                <th>Аудитория</th>
                <th>Длит.</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($slots as $row): ?>
            <?php $slot = $row['slot']; ?>
            <tr>
                <td><?= htmlspecialchars((string) $slot['date']) ?></td>
                <td><?= htmlspecialchars((string) $slot['time']) ?></td>
                <td><?= htmlspecialchars((string) $slot['teacher']) ?></td>
                <td><?= htmlspecialchars((string) $slot['room']) ?></td>
                <td><?= (int) ($slot['duration'] ?? 30) ?> мин</td>
                <td>
                    <?php if ($row['is_booked']): ?>
                        <span class="badge badge--busy">Занят</span>
                    <?php else: ?>
                        <span class="badge badge--free">Свободен</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!$row['is_booked']): ?>
                        <a class="btn"
                           href="/?route=/book&slot_id=<?= urlencode((string) $slot['id']) ?>">
                            Записаться
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>