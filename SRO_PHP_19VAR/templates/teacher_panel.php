<h1>Панель преподавателя</h1>
<div class="slot-card" style="background: #eef2f8;">
    <h3>Добавить слот</h3>
    <form method="post" action="index.php?route=/teacher" class="form" style="flex-direction: row; align-items: end;">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <label>Дата <input type="date" name="date" required min="<?= date('Y-m-d') ?>"></label>
        <label>Время <input type="time" name="time" required></label>
        <label>Длит. <input type="number" name="duration" value="30" min="15" max="120" required></label>
        <button type="submit" class="btn">Добавить</button>
    </form>
</div>
<h2>Расписание</h2>
<table class="slots">
    <thead><tr><th>Дата</th><th>Время</th><th>Длит.</th><th>Записано</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($slots as $slot): 
        $bookingsCount = count(get_slot_bookings($slot['id'])); 
        $isFull = $bookingsCount >= MAX_STUDENTS;
        $isPast = slot_start_datetime($slot) <= now(); 
    ?>
        <tr style="<?= $isPast ? 'opacity: 0.5;' : '' ?>">
            <td><?= htmlspecialchars($slot['date']) ?></td>
            <td><?= htmlspecialchars($slot['time']) ?></td>
            <td><?= htmlspecialchars((string)$slot['duration']) ?> мин</td>
            <td>
                <span class="badge badge--<?= $isFull ? 'busy' : 'free' ?>">
                    <?= $bookingsCount ?> / <?= MAX_STUDENTS ?>
                </span>
            </td>
            <td>
                <?php if ($bookingsCount === 0 && !$isPast): ?>
                    <form method="post" action="index.php?route=/teacher" onsubmit="return confirm('Удалить?');">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="slot_id" value="<?= htmlspecialchars($slot['id']) ?>">
                        <button type="submit" class="btn btn--danger" style="padding: 0.2rem 0.5rem;">Удалить</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>