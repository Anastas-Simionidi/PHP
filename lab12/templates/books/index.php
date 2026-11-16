<!DOCTYPE html>
<html lang="ru">
<head><title>Каталог книг</title></head>
<body>
 <h1>Каталог книг</h1>
 <a href="index.php?action=create">Добавить книгу</a>
 <form method="GET" action="index.php">
 <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Поиск...">
 <select name="sort">
 <option value="title" <?= ($_GET['sort'] ?? '') === 'title' ? 'selected' : '' ?>>По названию</option>
 <option value="isbn" <?= ($_GET['sort'] ?? '') === 'isbn' ? 'selected' : '' ?>>По ISBN</option>
 <option value="copies" <?= ($_GET['sort'] ?? '') === 'copies' ? 'selected' : '' ?>>По количеству</option>
 </select>
 <button type="submit">Найти</button>
 </form>
 <table border="1" cellpadding="5" style="margin-top:10px;">
 <tr>
 <th>ISBN</th><th>Название</th><th>Автор</th><th>Копии</th><th>Действия</th>
 </tr>
 <?php foreach ($books as $b): ?>
 <tr>
 <td><?= htmlspecialchars($b['isbn'], ENT_QUOTES, 'UTF-8') ?></td>
 <td><a href="index.php?action=show&id=<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8') ?></a></td>
 <td><?= htmlspecialchars($b['author'], ENT_QUOTES, 'UTF-8') ?></td>
 <td><?= (int)$b['copies'] ?></td>
 <td><a href="index.php?action=edit&id=<?= (int)$b['id'] ?>">Редактировать</a></td>
 </tr>
 <?php endforeach; ?>
 </table>
</body>
</html>
