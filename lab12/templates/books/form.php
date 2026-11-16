<!DOCTYPE html>
<html lang="ru">
<head><title><?= isset($book['id']) ? 'Редактировать' : 'Добавить' ?> книгу</title></head>
<body>
 <h1><?= isset($book['id']) ? 'Редактирование книги' : 'Добавление книги' ?></h1>
 <form method="POST" action="index.php?action=<?= isset($book['id']) ? 'update&id=' . $book['id'] : 'store' ?>">
 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
 
 <div>
 <label>ISBN:</label><br>
 <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">
 <?php if(isset($errors['isbn'])): ?><span style="color:red;"><?= $errors['isbn'] ?></span><?php endif; ?>
 </div>
 <div>
 <label>Название:</label><br>
 <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>">
 <?php if(isset($errors['title'])): ?><span style="color:red;"><?= $errors['title'] ?></span><?php endif; ?>
 </div>
 <div>
 <label>Автор:</label><br>
 <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>">
 <?php if(isset($errors['author'])): ?><span style="color:red;"><?= $errors['author'] ?></span><?php endif; ?>
 </div>
 <div>
 <label>Количество копий:</label><br>
 <input type="number" name="copies" value="<?= (int)$book['copies'] ?>">
 <?php if(isset($errors['copies'])): ?><span style="color:red;"><?= $errors['copies'] ?></span><?php endif; ?>
 </div>
 <br>
 <button type="submit">Сохранить</button>
 <a href="index.php">Отмена</a>
 </form>
</body>
</html>
