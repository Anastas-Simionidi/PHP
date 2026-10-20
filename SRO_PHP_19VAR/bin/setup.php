<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';

ensure_data_dir();

// 1. Пользователи (пароль у всех: 123456)
$users = [
    ['id' => 'u1', 'role' => 'admin', 'name' => 'Администратор', 'email' => 'admin@test.com', 'password' => password_hash('123456', PASSWORD_DEFAULT)],
    ['id' => 'u2', 'role' => 'teacher', 'name' => 'Рашидинов Д.Р.', 'email' => 'teacher@test.com', 'password' => password_hash('123456', PASSWORD_DEFAULT)],
    ['id' => 'u3', 'role' => 'student', 'name' => 'Иванов И.И.', 'email' => 'student@test.com', 'password' => password_hash('123456', PASSWORD_DEFAULT)],
];
write_json_atomic(USERS_FILE, $users);

// 2. Аудитории
$rooms = [
    ['id' => 'r1', 'name' => 'A-101'],
    ['id' => 'r2', 'name' => 'B-204'],
    ['id' => 'r3', 'name' => 'C-310'],
];
write_json_atomic(ROOMS_FILE, $rooms);

// 3. Слоты (создаем на 3 дня вперед)
$slots = [];
$tz = tz();
$now = new DateTimeImmutable('now', $tz);
$n = 1;
for ($d = 1; $d <= 3; $d++) {
    $day = $now->modify("+$d day");
    if ((int)$day->format('N') > 5) continue;
    foreach (['09:00', '11:00', '14:00'] as $t) {
        $slots[] = [
            'id' => sprintf('s%03d', $n++),
            'teacher_id' => 'u2',
            'date' => $day->format('Y-m-d'),
            'time' => $t,
            'duration' => 30,
        ];
    }
}
write_json_atomic(SLOTS_FILE, $slots);
write_json_atomic(BOOKINGS_FILE, []);
echo "База данных успешно пересоздана!\n";