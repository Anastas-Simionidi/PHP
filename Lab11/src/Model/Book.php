<?php
declare(strict_types=1);

namespace University\CourseCatalog\Model;

use University\CourseCatalog\Exception\InvalidLibraryDataException;

final class Book
{
    public function __construct(
        public readonly string $isbn,
        public readonly string $title,
        public readonly string $author,
        public int $copies
    ) {
        if (preg_match('/^\d{3}-\d-\d{2}-\d{6}-\d$/', $isbn) !== 1) {
            throw new InvalidLibraryDataException('Неверный формат ISBN. Используйте шаблон: 123-4-56-789012-3');
        }
        if (trim($title) === '') {
            throw new InvalidLibraryDataException('Название книги не может быть пустым.');
        }
        if ($copies < 0) {
            throw new InvalidLibraryDataException('Количество копий не может быть отрицательным.');
        }
    }
}
