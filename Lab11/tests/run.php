<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';
use University\CourseCatalog\Model\Book;
use University\CourseCatalog\Model\Member;
use University\CourseCatalog\Repository\InMemoryBookRepository;
use University\CourseCatalog\Service\LoanService;
use University\CourseCatalog\Exception\InvalidLibraryDataException;

function expectException(callable $action, string $expectedExceptionClass): void
{
    try {
        $action();
        throw new \RuntimeException("Ожидалось исключение класса: $expectedExceptionClass");
    } catch (\Throwable $error) {
        if (!$error instanceof $expectedExceptionClass) {
            throw $error;
        }
    }
}

$repo = new InMemoryBookRepository();
$service = new LoanService($repo);

// Тест 1: Добавление книги и поиск
$book = new Book('111-1-11-111111-1', 'Clean Architecture', 'Robert Martin', 1);
$repo->save($book);
if ($repo->findByIsbn('111-1-11-111111-1')?->title !== 'Clean Architecture') {
    throw new \RuntimeException('Ошибка: Книга не найдена в репозитории.');
}

// Тест 2: Исключение на некорректный формат ISBN
expectException(
    fn() => new Book('неверный-isbn', 'Тест', 'Автор', 5),
    InvalidLibraryDataException::class
);

// Тест 3: Выдача книги читателю
$member = new Member('M-9999', 'Тестовый Студент');
$service->loanBook($member, '111-1-11-111111-1');

if ($book->copies !== 0 || !isset($member->borrowedBooks['111-1-11-111111-1'])) {
    throw new \RuntimeException('Ошибка: Книга не списалась у библиотеки или не добавилась читателю.');
}

// Тест 4: Отказ в выдаче, если копий больше нет
expectException(
    fn() => $service->loanBook($member, '111-1-11-111111-1'),
    \LogicException::class
);

echo "🎉 Все автоматические тесты успешно пройдены!\n";
