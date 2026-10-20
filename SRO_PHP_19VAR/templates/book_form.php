<h1>Запись на консультацию</h1>
<div class="slot-card">
    <p><strong>Преподаватель:</strong> <?= htmlspecialchars($teacher_name) ?></p>
    <p><strong>Время:</strong> <?= htmlspecialchars($slot['date'] . ' ' . $slot['time']) ?></p>
    <p><strong>Уже записано:</strong> <?= $bookings_count ?> из <?= MAX_STUDENTS ?> чел.</p>
</div>
<form method="post" action="index.php?route=/book" class="form">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <input type="hidden" name="slot_id" value="<?= htmlspecialchars($slot['id']) ?>">
    
    <h3>Выберите аудиторию:</h3>
    <div class="rooms-grid">
        <?php foreach ($rooms as $room): ?>
            <label class="room-free">
                <input type="radio" name="room_id" value="<?= htmlspecialchars($room['id']) ?>" required>
                <?= htmlspecialchars($room['name']) ?>
            </label>
        <?php endforeach; ?>
    </div>
    <label>Комментарий <textarea name="comment" rows="3" maxlength="500"></textarea></label>
    <button type="submit" class="btn">Подтвердить</button>
</form>