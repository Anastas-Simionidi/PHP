<?php
declare(strict_types=1);

namespace University\CourseCatalog\Service;

use University\CourseCatalog\Contract\BookRepositoryInterface;
use University\CourseCatalog\Model\Book;
use University\CourseCatalog\Model\Member;

final readonly class LoanService
{
    public function __construct(
        private BookRepositoryInterface $bookRepository
    ) {}

    public function loanBook(Member $member, string $isbn): bool
    {
        $book = $this->bookRepository->findByIsbn($isbn);
        
        if (!$book) {
            throw new \LogicException("Книга с ISBN $isbn не найдена в библиотеке.");
        }
        if ($book->copies <= 0) {
            throw new \LogicException("Нет доступных экземпляров книги '{$book->title}'.");
        }
        if (isset($member->borrowedBooks[$isbn])) {
            throw new \LogicException("Читатель уже взял один экземпляр этой книги.");
        }

        $book->copies--;
        $member->borrowedBooks[$isbn] = $book;
        return true;
    }

    public function returnBook(Member $member, string $isbn): bool
    {
        if (!isset($member->borrowedBooks[$isbn])) {
            throw new \LogicException("У читателя нет книги с ISBN $isbn.");
        }

        $book = $this->bookRepository->findByIsbn($isbn);
        if ($book) {
            $book->copies++;
        }

        unset($member->borrowedBooks[$isbn]);
        return true;
    }
}
