<!DOCTYPE html>
<html lang="ru">
<head><title><?= htmlspecialchars($book['title']) ?></title></head>
<body>
 <h1><?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?></h1>
 <p><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn'], ENT_QUOTES, 'UTF-8') ?></p>
 <p><strong>Автор:</strong> <?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8') ?></p>
 <p><strong>Доступно копий:</strong> <?= (int)$book['copies'] ?></p>

 <form method="POST" action="index.php?action=issue" style="display:inline;">
 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
 <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
 <button type="submit" <?= $book['copies'] <= 0 ? 'disabled' : '' ?>>Выдать книгу (Транзакция)</button>
 </form>

 <hr>
 <form method="POST" action="index.php?action=delete" onsubmit="return confirm('Вы уверены?');" style="display:inline;">
 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
 <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
 <button type="submit" style="color:red;">Удалить запись</button>
 </form>
 <br><br>
 <a href="index.php">Назад к списку</a>
</body>
</html>
