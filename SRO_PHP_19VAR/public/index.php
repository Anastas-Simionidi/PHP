<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';

$route = $_GET['route'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($route) {
        case '/login':
            if ($method === 'POST') handle_login();
            else render('login', ['title' => 'Вход']);
            break;
        case '/register':
            if ($method === 'POST') handle_register();
            else render('register', ['title' => 'Регистрация']);
            break;
        case '/logout':
            session_destroy(); header('Location: index.php?route=/login'); exit;
        case '/':
            show_slots(); break;
        case '/book':
            require_role('student');
            if ($method === 'POST') handle_book_post();
            else show_book_form();
            break;
        case '/my':
            if (!current_user()) { header('Location: index.php?route=/login'); exit; }
            show_my(); break;
        case '/cancel':
            require_role('student');
            if ($method === 'POST') handle_cancel_post();
            break;
        case '/teacher':
            require_role('teacher');
            if ($method === 'POST') handle_teacher_post();
            else show_teacher_panel();
            break;
        case '/admin':
            require_role('admin');
            if ($method === 'POST') handle_admin_post();
            else render('admin', ['title' => 'Админка', 'rooms' => get_all_rooms(), 'allBookings' => get_all_bookings_with_details()]);
            break;
        default:
            render_error(404, 'Страница не найдена');
    }
} catch (Throwable $e) {
    error_log((string)$e);
    render_error(500, 'Ошибка сервера');
}

function handle_login(): void {
    csrf_check($_POST['csrf'] ?? null);
    if (login(normalize_string($_POST['email'] ?? ''), $_POST['password'] ?? '')) { header('Location: index.php'); exit; }
    flash_set('error', 'Неверный email или пароль'); header('Location: index.php?route=/login'); exit;
}
function handle_register(): void {
    csrf_check($_POST['csrf'] ?? null);
    $name = normalize_string($_POST['name'] ?? ''); $email = normalize_string($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? ''; $role = $_POST['role'] ?? 'student';
    if (!$name || !$email || !$pass) { flash_set('error', 'Заполните все поля.'); header('Location: index.php?route=/register'); exit; }
    try {
        register_user($name, $email, $pass, $role);
        flash_set('success', 'Успешно! Теперь войдите.'); header('Location: index.php?route=/login'); exit;
    } catch (DomainException $e) {
        flash_set('error', $e->getMessage()); header('Location: index.php?route=/register'); exit;
    }
}
function show_slots(): void {
    render('slots', ['title' => 'Слоты', 'slots' => upcoming_slots_with_status(), 'user' => current_user()]);
}
function show_book_form(): void {
    $slotId = $_GET['slot_id'] ?? ''; $slot = find_slot($slotId);
    if (!$slot || slot_start_datetime($slot) <= now() || count(get_slot_bookings($slotId)) >= MAX_STUDENTS) {
        render_error(400, 'Слот недоступен или уже заполнен.'); return;
    }
    render('book_form', [
        'title' => 'Запись', 'slot' => $slot, 'teacher_name' => get_user_name($slot['teacher_id']),
        'rooms' => get_all_rooms(), 'bookings_count' => count(get_slot_bookings($slotId))
    ]);
}
function handle_book_post(): void {
    csrf_check($_POST['csrf'] ?? null);
    $user = current_user(); $slotId = $_POST['slot_id'] ?? ''; $roomId = $_POST['room_id'] ?? '';
    if ($roomId === '') { flash_set('error', 'Выберите аудиторию.'); header("Location: index.php?route=/book&slot_id=$slotId"); exit; }
    try { book_slot($slotId, $roomId, $user['id'], normalize_string($_POST['comment'] ?? '')); flash_set('success', 'Записаны!'); header('Location: index.php?route=/my'); exit; }
    catch (DomainException $e) { flash_set('error', $e->getMessage()); header("Location: index.php?route=/book&slot_id=$slotId"); exit; }
}
function show_my(): void {
    $user = current_user();
    render('my', ['title' => 'Записи', 'user' => $user, 'bookings' => $user['role'] === 'teacher' ? teacher_bookings($user['id']) : own_bookings($user['id'])]);
}
function handle_cancel_post(): void {
    csrf_check($_POST['csrf'] ?? null);
    try { cancel_booking($_POST['booking_id'] ?? '', current_user()['id']); flash_set('success', 'Отменено.'); }
    catch (DomainException $e) { flash_set('error', $e->getMessage()); }
    header('Location: index.php?route=/my'); exit;
}
function show_teacher_panel(): void {
    render('teacher_panel', ['title' => 'Панель препода', 'slots' => get_teacher_slots(current_user()['id'])]);
}
function handle_teacher_post(): void {
    csrf_check($_POST['csrf'] ?? null);
    try {
        if (($_POST['action'] ?? '') === 'delete') delete_slot($_POST['slot_id'] ?? '', current_user()['id']);
        else create_slot(current_user()['id'], $_POST['date'] ?? '', $_POST['time'] ?? '', (int)($_POST['duration'] ?? 30));
        flash_set('success', 'Готово.');
    } catch (DomainException $e) { flash_set('error', $e->getMessage()); }
    header('Location: index.php?route=/teacher'); exit;
}
function handle_admin_post(): void {
    csrf_check($_POST['csrf'] ?? null);
    $name = normalize_string($_POST['room_name'] ?? '');
    if ($name !== '') {
        modify_json(ROOMS_FILE, fn($r) => array_merge($r, [['id' => 'r_' . bin2hex(random_bytes(3)), 'name' => $name]]));
        flash_set('success', 'Аудитория добавлена.');
    }
    header('Location: index.php?route=/admin'); exit;
}
function render(string $template, array $vars): void {
    $templatePath = __DIR__ . '/../templates/' . $template . '.php';
    extract($vars, EXTR_SKIP); require __DIR__ . '/../templates/layout.php';
}
function render_error(int $code, string $msg): void {
    http_response_code($code); render('error', ['title' => 'Ошибка', 'message' => $msg]);
}