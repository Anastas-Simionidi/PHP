<?php
declare(strict_types=1);

namespace University\CourseCatalog\Model;

use University\CourseCatalog\Exception\InvalidLibraryDataException;

final class Member
{
    /** @var array<string, Book> Хранилище взятых книг по их ISBN */
    public array $borrowedBooks = [];

    public function __construct(
        public readonly string $memberId,
        public readonly string $name
    ) {
        if (preg_match('/^M-\d{4}$/', $memberId) !== 1) {
            throw new InvalidLibraryDataException('Неверный ID читателя. Шаблон: M-XXXX (например, M-1001)');
        }
        if (trim($name) === '') {
            throw new InvalidLibraryDataException('Имя читателя не может быть пустым.');
        }
    }
}
