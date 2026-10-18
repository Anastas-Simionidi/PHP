<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

ensure_data_dir();

if (!is_file(BOOKINGS_FILE)) {
    write_json_atomic(BOOKINGS_FILE, []);
    fwrite(STDOUT, "Создан пустой bookings.json\n");
}

$force = in_array('--force', $argv ?? [], true);
if (!is_file(SLOTS_FILE) || $force) {
    $tz        = tz();
    $now       = new DateTimeImmutable('now', $tz);
    $teachers  = ['Рашидов Д.Р.', 'Ахметова Г.К.', 'Иванов П.С.'];
    $rooms     = ['A-101', 'B-204', 'C-310'];
    $times     = ['09:00', '11:00', '14:00', '16:00'];
    $slots     = [];
    $n         = 1;

    for ($d = 1; $d <= 14; $d++) {
        $day = $now->modify("+$d day");
        if ((int) $day->format('N') > 5) {
            continue;
        }
        foreach ($times as $i => $t) {
            $slots[] = [
                'id'       => sprintf('s%03d', $n++),
                'teacher'  => $teachers[$i % count($teachers)],
                'date'     => $day->format('Y-m-d'),
                'time'     => $t,
                'duration' => 30,
                'room'     => $rooms[$i % count($rooms)],
            ];
        }
    }

    write_json_atomic(SLOTS_FILE, $slots);
    fwrite(STDOUT, 'Сгенерировано слотов: ' . count($slots) . "\n");
}