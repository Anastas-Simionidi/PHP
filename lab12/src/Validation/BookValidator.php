<?php
declare(strict_types=1);

namespace App\Validation;

final class BookValidator
{
 public static function validate(array $input): array
 {
 $errors = [];

 $isbn = trim((string)($input['isbn'] ?? ''));
 $title = trim((string)($input['title'] ?? ''));
 $author = trim((string)($input['author'] ?? ''));
 $copies = filter_var($input['copies'] ?? null, FILTER_VALIDATE_INT);

 if (!preg_match('/^(97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]{1,7}[- ]?[0-9]{1,7}[- ]?[0-9X]$/', $isbn)) {
 $errors['isbn'] = 'Неверный формат ISBN.';
 }
 if ($title === '' || mb_strlen($title) > 180) {
 $errors['title'] = 'Название обязательно, максимум 180 символов.';
 }
 if ($author === '' || mb_strlen($author) > 180) {
 $errors['author'] = 'Автор обязателен, максимум 180 символов.';
 }
 if ($copies === false || $copies < 0) {
 $errors['copies'] = 'Количество копий должно быть целым неотрицательным числом.';
 }

 return [
 'errors' => $errors,
 'sanitized' => [
 'isbn' => $isbn,
 'title' => $title,
 'author' => $author,
 'copies' => $copies
 ]
 ];
 }
}
