<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$route = is_string($_GET['route'] ?? null) ? $_GET['route'] : '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    switch ($route) {
        case '/':
            show_slots();
            break;

        case '/book':
            if ($method === 'POST') {
                handle_book_post();
            } else {
                show_book_form();
            }
            break;

        case '/my':
            show_my();
            break;

        case '/cancel':
            if ($method === 'POST') {
                handle_cancel_post();
            } else {
                http_response_code(405);
                exit('Method Not Allowed');
            }
            break;

        default:
            render_error(404, 'Страница не найдена.');
    }
} catch (RuntimeException $e) {
    error_log((string) $e);
    render_error(500, 'Временная ошибка хранилища. Попробуйте позже.');
} catch (Throwable $e) {
    error_log((string) $e);
    render_error(500, 'Внутренняя ошибка сервера.');
}

/* ---------- Handlers ---------- */

function show_slots(): void
{
    render('slots', [
        'title' => 'Свободные слоты',
        'slots' => upcoming_slots_with_status(),
        'flash' => flash_take(),
    ]);
}

function show_book_form(): void
{
    $slotId = is_string($_GET['slot_id'] ?? null) ? $_GET['slot_id'] : '';
    if ($slotId === '' || !preg_match('/^[A-Za-z0-9_\-]{1,32}$/', $slotId)) {
        render_error(400, 'Не указан корректный слот.');
        return;
    }
    $slot = find_slot($slotId);
    if ($slot === null) {
        render_error(404, 'Слот не найден.');
        return;
    }
    if (slot_start_datetime($slot) <= now()) {
        render_error(410, 'Этот слот уже прошёл.');
        return;
    }
    if (active_booking_for_slot(load_bookings(), $slotId) !== null) {
        render_error(409, 'Слот уже занят.');
        return;
    }

    render('book_form', [
        'title'  => 'Запись на консультацию',
        'slot'   => $slot,
        'flash'  => flash_take(),
        'errors' => [],
        'values' => [
            'slot_id'      => $slotId,
            'student_name' => '',
            'email'        => current_email() ?? '',
            'comment'      => '',
        ],
    ]);
}

function handle_book_post(): void
{
    csrf_check(is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : null);

    $validation = validate_booking_input($_POST);

    if ($validation['errors'] !== []) {
        $slot = find_slot($validation['data']['slot_id']);
        if ($slot === null) {
            render_error(400, 'Некорректные данные формы.');
            return;
        }
        render('book_form', [
            'title'  => 'Запись на консультацию',
            'slot'   => $slot,
            'flash'  => null,
            'errors' => $validation['errors'],
            'values' => $validation['data'],
        ]);
        return;
    }

    try {
        book_slot($validation['data']['slot_id'], $validation['data']);
    } catch (DomainException $e) {
        flash_set('error', $e->getMessage());
        header('Location: /?route=/book&slot_id=' . urlencode($validation['data']['slot_id']));
        exit;
    }

    remember_email($validation['data']['email']);
    flash_set('success', 'Вы успешно записаны на консультацию.');
    header('Location: /?route=/my');
    exit;
}

function show_my(): void
{
    $email = current_email();
    render('my', [
        'title'    => 'Мои записи',
        'email'    => $email,
        'bookings' => $email === null ? [] : own_bookings($email),
        'flash'    => flash_take(),
    ]);
}

function handle_cancel_post(): void
{
    csrf_check(is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : null);

    $email = current_email();
    if ($email === null) {
        render_error(403, 'Нет активной сессии.');
        return;
    }
    $bookingId = is_string($_POST['booking_id'] ?? null) ? $_POST['booking_id'] : '';
    if (!preg_match('/^b_[a-f0-9]{12}$/', $bookingId)) {
        flash_set('error', 'Некорректный идентификатор записи.');
        header('Location: /?route=/my');
        exit;
    }

    try {
        cancel_booking($bookingId, $email);
        flash_set('success', 'Запись успешно отменена.');
    } catch (DomainException $e) {
        flash_set('error', $e->getMessage());
    }

    header('Location: /?route=/my');
    exit;
}

/* ---------- Rendering ---------- */

/** @param array<string,mixed> $vars */
function render(string $template, array $vars): void
{
    $templatePath = __DIR__ . '/../templates/' . $template . '.php';
    extract($vars, EXTR_SKIP);
    require __DIR__ . '/../templates/layout.php';
}

function render_error(int $code, string $message): void
{
    http_response_code($code);
    render('error', [
        'title'   => 'Ошибка ' . $code,
        'message' => $message,
        'flash'   => flash_take(),
    ]);
}