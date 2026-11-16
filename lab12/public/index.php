<?php
declare(strict_types=1);

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});


use App\Database\ConnectionFactory;
use App\Repository\BookRepository;
use App\Validation\BookValidator;

session_start();

// Инициализация CSRF токена
if (empty($_SESSION['csrf_token'])) {
 $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Загрузка переменных среды (для демонстрации жестко захардкожены дефолты в фабрике)
try {
 $pdo = ConnectionFactory::create();
 $repository = new BookRepository($pdo);
} catch (\Throwable $e) {
 http_response_code(503);
 exit('Сервис временно недоступен. Ошибка конфигурации.'); // Нейтральное сообщение без деталей PDO
}

$action = $_GET['action'] ?? 'index';
$allowed = ['index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'issue'];

if (!in_array($action, $allowed, true)) {
 http_response_code(404);
 exit('Страница не найдена');
}

// Изменяющие методы требуют POST
if (in_array($action, ['store', 'update', 'delete', 'issue'], true) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
 http_response_code(405);
 exit('Метод не разрешён');
}

// Проверка CSRF
if (in_array($action, ['store', 'update', 'delete', 'issue'], true)) {
 if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
 http_response_code(403);
 exit('Ошибка CSRF-токена.');
 }
}

// Логика Контроллера
switch ($action) {
 case 'index':
 $search = $_GET['search'] ?? '';
 $sort = $_GET['sort'] ?? 'title';
 $books = $repository->findAll($search, $sort);
 include __DIR__ . '/../templates/books/index.php';
 break;

 case 'show':
 $id = (int)($_GET['id'] ?? 0);
 $book = $repository->find($id);
 if (!$book) {
 http_response_code(404);
 exit('Книга не найдена');
 }
 include __DIR__ . '/../templates/books/show.php';
 break;

 case 'create':
 $errors = [];
 $book = ['isbn' => '', 'title' => '', 'author' => '', 'copies' => 1];
 include __DIR__ . '/../templates/books/form.php';
 break;

 case 'store':
 $result = BookValidator::validate($_POST);
 if (!empty($result['errors'])) {
 $errors = $result['errors'];
 $book = $_POST;
 include __DIR__ . '/../templates/books/form.php';
 } else {
 try {
 $repository->create($result['sanitized']);
 header('Location: index.php?action=index', true, 303);
 exit;
 } catch (\PDOException $e) {
 if ($e->getCode() === '23000') { // Duplicate entry
 $errors = ['isbn' => 'Данный ISBN уже существует в базе.'];
 $book = $_POST;
 include __DIR__ . '/../templates/books/form.php';
 } else {
 http_response_code(500);
 exit('Системная ошибка при сохранении данных.');
 }
 }
 }
 break;

 case 'edit':
 $id = (int)($_GET['id'] ?? 0);
 $book = $repository->find($id);
 if (!$book) {
 http_response_code(404);
 exit('Книга не найдена');
 }
 $errors = [];
 include __DIR__ . '/../templates/books/form.php';
 break;

 case 'update':
 $id = (int)($_GET['id'] ?? 0);
 $result = BookValidator::validate($_POST);
 if (!empty($result['errors'])) {
 $errors = $result['errors'];
 $book = $_POST;
 $book['id'] = $id;
 include __DIR__ . '/../templates/books/form.php';
 } else {
 $repository->update($id, $result['sanitized']);
 header("Location: index.php?action=show&id={$id}", true, 303);
 exit;
 }
 break;

 case 'delete':
 $id = (int)($_POST['id'] ?? 0);
 $repository->delete($id);
 header('Location: index.php?action=index', true, 303);
 exit;

 case 'issue':
 $id = (int)($_POST['id'] ?? 0);
 try {
 $repository->issueBookWithAudit($id);
 header("Location: index.php?action=show&id={$id}", true, 303);
 exit;
 } catch (\Throwable $e) {
 http_response_code(400);
 exit('Ошибка транзакции: ' . htmlspecialchars($e->getMessage()));
 }
}
