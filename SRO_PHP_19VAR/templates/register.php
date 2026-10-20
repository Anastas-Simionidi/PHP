<h1>Регистрация</h1>
<form method="post" action="index.php?route=/register" class="form">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <label>ФИО <input type="text" name="name" required maxlength="100"></label>
    <label>Email <input type="email" name="email" required></label>
    <label>Пароль <input type="password" name="password" required minlength="6"></label>
    <label>Роль
        <select name="role" style="padding: .5rem; border-radius: 6px; border: 1px solid var(--border);">
            <option value="student">Студент</option>
            <option value="teacher">Преподаватель</option>
        </select>
    </label>
    <button type="submit" class="btn">Создать аккаунт</button>
</form>
<p style="margin-top: 1rem;">Уже есть аккаунт? <a href="index.php?route=/login">Войти</a></p>