<?php
declare(strict_types=1);

namespace University\CourseCatalog\Contract;

use University\CourseCatalog\Model\Book;

interface BookRepositoryInterface
{
    public function save(Book $book): void;
    public function findByIsbn(string $isbn): ?Book;
    /** @return list<Book> */
    public function all(): array;
}
