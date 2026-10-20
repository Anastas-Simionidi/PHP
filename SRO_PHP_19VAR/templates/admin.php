<h1>Админка</h1>
<div class="slot-card">
    <h3>Добавить аудиторию</h3>
    <form method="post" action="index.php?route=/admin" class="form" style="flex-direction: row;">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <label>Название <input type="text" name="room_name" required></label>
        <button type="submit" class="btn">Добавить</button>
    </form>
</div>
<h2>Все записи студентов</h2>
<table class="slots">
    <thead><tr><th>Студент</th><th>Преподаватель</th><th>Время</th><th>Ауд.</th><th>Статус</th></tr></thead>
    <tbody>
    <?php foreach ($allBookings as $b): ?>
        <tr style="<?= $b['status'] === 'cancelled' ? 'opacity: 0.5;' : '' ?>">
            <td><?= htmlspecialchars($b['student_name']) ?><br><small><?= htmlspecialchars($b['student_email']) ?></small></td>
            <td><?= htmlspecialchars($b['teacher_name']) ?></td>
            <td><?= htmlspecialchars($b['date'] . ' ' . $b['time']) ?></td>
            <td><?= htmlspecialchars($b['room_name']) ?></td>
            <td><span class="badge badge--<?= $b['status'] === 'active' ? 'free' : 'busy' ?>"><?= $b['status'] ?></span></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>