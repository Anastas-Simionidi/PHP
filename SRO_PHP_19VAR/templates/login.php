<h1>Вход</h1>
<form method="post" action="index.php?route=/login" class="form">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <label>Email <input type="email" name="email" required></label>
    <label>Пароль <input type="password" name="password" required></label>
    <button type="submit" class="btn">Войти</button>
</form>
<p style="margin-top: 1rem;">Нет аккаунта? <a href="index.php?route=/register">Зарегистрироваться</a></p>
<div class="hint" style="margin-top: 1rem;">
    <i>Тестовые: admin@test.com / teacher@test.com / student@test.com (пароль 123456)</i>
</div>