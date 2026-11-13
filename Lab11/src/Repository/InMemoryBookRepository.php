<?php
declare(strict_types=1);

namespace University\CourseCatalog\Repository;

use University\CourseCatalog\Contract\BookRepositoryInterface;
use University\CourseCatalog\Model\Book;

final class InMemoryBookRepository implements BookRepositoryInterface
{
    /** @var array<string, Book> */
    private array $books = [];

    public function save(Book $book): void
    {
        $this->books[$book->isbn] = $book;
    }

    public function findByIsbn(string $isbn): ?Book
    {
        return $this->books[$isbn] ?? null;
    }

    public function all(): array
    {
        return array_values($this->books);
    }
}
