<?php

declare(strict_types=1);

function normalize_string(mixed $value): string
{
    if (!is_string($value)) {
        return '';
    }
    return trim((string) preg_replace('/\s+/u', ' ', $value));
}

/**
 * @param array<string,mixed> $input
 * @return array{errors:array<string,string>, data:array<string,string>}
 */
function validate_booking_input(array $input): array
{
    $errors = [];

    $name    = normalize_string($input['student_name'] ?? '');
    $email   = mb_strtolower(normalize_string($input['email'] ?? ''));
    $comment = normalize_string($input['comment'] ?? '');
    $slotId  = normalize_string($input['slot_id'] ?? '');

    if ($name === '') {
        $errors['student_name'] = 'Укажите имя.';
    } elseif (mb_strlen($name) < 2) {
        $errors['student_name'] = 'Имя слишком короткое.';
    } elseif (mb_strlen($name) > 120) {
        $errors['student_name'] = 'Не длиннее 120 символов.';
    }

    if ($email === '') {
        $errors['email'] = 'Укажите email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный email.';
    } elseif (mb_strlen($email) > 254) {
        $errors['email'] = 'Email слишком длинный.';
    }

    if (mb_strlen($comment) > 500) {
        $errors['comment'] = 'Комментарий не длиннее 500 символов.';
    }

    if ($slotId === '' || !preg_match('/^[A-Za-z0-9_\-]{1,32}$/', $slotId)) {
        $errors['slot_id'] = 'Некорректный идентификатор слота.';
    }

    return [
        'errors' => $errors,
        'data'   => [
            'student_name' => $name,
            'email'        => $email,
            'comment'      => $comment,
            'slot_id'      => $slotId,
        ],
    ];
}