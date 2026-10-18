<?php

declare(strict_types=1);

/** @var string|null $email */
/** @var array<int,array<string,mixed>> $bookings */
?>
<h1>Мои записи</h1>

<?php if ($email === null): ?>
    <p class="empty">Вы ещё не записывались. <a href="/">Выбрать слот</a></p>
<?php elseif ($bookings === []): ?>
    <p class="empty">У вас пока нет записей. <a href="/">Выбрать слот</a></p>
<?php else: ?>
    <p class="hint">Записи для <strong><?= htmlspecialchars($email) ?></strong></p>
    <table class="slots">
        <thead>
        <tr>
            <th>Слот</th>
            <th>Дата и время</th>
            <th>Статус</th>
            <th>Создана</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <?php
                $isActive   = ($b['status'] ?? '') === 'active';
                $start      = $b['start'];
                $deadline   = $start->sub(new DateInterval('PT' . CANCEL_MINUTES_BEFORE . 'M'));
                $canCancel  = $isActive && now() < $deadline;
                $slot       = $b['slot'];
            ?>
            <tr>
                <td>
                    <?= htmlspecialchars((string) $slot['teacher']) ?>,
                    <?= htmlspecialchars((string) $slot['room']) ?>
                </td>
                <td><?= htmlspecialchars((string) $slot['date'] . ' ' . (string) $slot['time']) ?></td>
                <td>
                    <?php if ($isActive): ?>
                        <span class="badge badge--free">Активна</span>
                    <?php else: ?>
                        <span class="badge badge--busy">Отменена</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars((string) $b['created_at']) ?></td>
                <td>
                    <?php if ($canCancel): ?>
                        <form method="post" action="/?route=/cancel"
                              onsubmit="return confirm('Отменить запись?');">
                            <input type="hidden" name="csrf"
                                   value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="booking_id"
                                   value="<?= htmlspecialchars((string) $b['id']) ?>">
                            <button type="submit" class="btn btn--danger">Отменить</button>
                        </form>
                    <?php elseif ($isActive): ?>
                        <span class="hint">Отмена недоступна</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if (!empty($b['history']) && is_array($b['history'])): ?>
                <tr>
                    <td colspan="5" class="history">
                        <strong>История:</strong>
                        <?php foreach ($b['history'] as $h): ?>
                            <span class="history__item">
                                <?= htmlspecialchars((string) ($h['action'] ?? '')) ?>
                                — <?= htmlspecialchars((string) ($h['at'] ?? '')) ?>
                            </span>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>