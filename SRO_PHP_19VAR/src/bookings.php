<?php
declare(strict_types=1);

function tz(): DateTimeZone { return new DateTimeZone(TZ_NAME); }
function now(): DateTimeImmutable { return new DateTimeImmutable('now', tz()); }
function slot_start_datetime(array $slot): DateTimeImmutable {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i', (string)$slot['date'] . ' ' . (string)$slot['time'], tz());
    if ($dt === false) throw new RuntimeException('Некорректные дата/время.');
    return $dt;
}
function load_all_slots(): array {
    return array_values(array_filter(read_json_array(SLOTS_FILE), static fn($s) => is_array($s) && isset($s['id'])));
}
function find_slot(string $slotId): ?array {
    foreach (load_all_slots() as $slot) { if (($slot['id'] ?? null) === $slotId) return $slot; }
    return null;
}
function load_bookings(): array {
    return array_values(array_filter(read_json_array(BOOKINGS_FILE), static fn($b) => is_array($b) && isset($b['id'])));
}
function get_all_rooms(): array { return read_json_array(ROOMS_FILE); }
function get_user_name(string $userId): string {
    foreach (read_json_array(USERS_FILE) as $u) { if ($u['id'] === $userId) return $u['name']; }
    return 'Неизвестный';
}

function get_slot_bookings(string $slotId): array {
    $active = [];
    foreach (load_bookings() as $b) {
        if (($b['slot_id'] ?? '') === $slotId && ($b['status'] ?? '') === 'active') {
            $active[] = $b;
        }
    }
    return $active;
}

function book_slot(string $slotId, string $roomId, string $studentId, string $comment): array {
    $slot = find_slot($slotId);
    if (!$slot) throw new DomainException('Слот не найден.');

    $currentBookings = get_slot_bookings($slotId);
    
    if (count($currentBookings) >= MAX_STUDENTS) {
        throw new DomainException('На эту консультацию уже записалось максимальное количество студентов (' . MAX_STUDENTS . ').');
    }

    foreach ($currentBookings as $b) {
        if ($b['student_id'] === $studentId) {
            throw new DomainException('Вы уже записаны на эту консультацию.');
        }
    }

    $booking = [
        'id' => 'b_' . bin2hex(random_bytes(6)), 
        'slot_id' => $slotId, 
        'room_id' => $roomId,
        'student_id' => $studentId, 
        'comment' => $comment, 
        'status' => 'active',
        'created_at' => now()->format(DateTimeInterface::ATOM),
        'history' => [['action' => 'created', 'at' => now()->format(DateTimeInterface::ATOM)]]
    ];
    
    modify_json(BOOKINGS_FILE, static fn(array $bookings): array => array_merge($bookings, [$booking]));
    return $booking;
}

function cancel_booking(string $bookingId, string $studentId): void {
    modify_json(BOOKINGS_FILE, static function (array $bookings) use ($bookingId, $studentId): array {
        foreach ($bookings as $i => $b) {
            if (($b['id'] ?? null) !== $bookingId) continue;
            if (($b['student_id'] ?? '') !== $studentId) throw new DomainException('Не ваша запись.');
            if (($b['status'] ?? '') !== 'active') throw new DomainException('Уже отменена.');
            $slot = find_slot((string) $b['slot_id']);
            if (now() >= slot_start_datetime($slot)->sub(new DateInterval('PT' . CANCEL_MINUTES_BEFORE . 'M'))) {
                throw new DomainException('Поздно отменять (менее ' . CANCEL_MINUTES_BEFORE . ' мин).');
            }
            $bookings[$i]['status'] = 'cancelled';
            $bookings[$i]['history'][] = ['action' => 'cancelled', 'at' => now()->format(DateTimeInterface::ATOM)];
            return $bookings;
        }
        throw new DomainException('Запись не найдена.');
    });
}

function upcoming_slots_with_status(): array {
    $n = now(); $out = [];
    foreach (load_all_slots() as $slot) {
        $start = slot_start_datetime($slot);
        if ($start <= $n) continue;
        
        $count = count(get_slot_bookings((string) $slot['id']));
        
        $out[] = [
            'slot' => $slot, 
            'start' => $start,
            'bookings_count' => $count,
            'is_full' => $count >= MAX_STUDENTS,
            'teacher_name' => get_user_name($slot['teacher_id'])
        ];
    }
    usort($out, static fn($a, $b) => $a['start'] <=> $b['start']); return $out;
}

function own_bookings(string $studentId): array {
    $out = [];
    foreach (load_bookings() as $b) {
        if (($b['student_id'] ?? '') !== $studentId) continue;
        $slot = find_slot((string) ($b['slot_id'] ?? '')); if (!$slot) continue;
        $b['slot'] = $slot; $b['start'] = slot_start_datetime($slot);
        foreach(get_all_rooms() as $r) if($r['id'] === $b['room_id']) $b['room_name'] = $r['name'];
        $out[] = $b;
    }
    usort($out, static fn($a, $b) => $b['start'] <=> $a['start']); return $out;
}

function teacher_bookings(string $teacherId): array {
    $out = [];
    foreach (load_bookings() as $b) {
        $slot = find_slot($b['slot_id']);
        if ($slot && $slot['teacher_id'] === $teacherId) {
            $b['slot'] = $slot; $b['start'] = slot_start_datetime($slot);
            $b['student_name'] = get_user_name($b['student_id']);
            foreach(get_all_rooms() as $r) if($r['id'] === $b['room_id']) $b['room_name'] = $r['name'];
            $out[] = $b;
        }
    }
    return $out;
}

function create_slot(string $teacherId, string $date, string $time, int $duration): void {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i', "$date $time", tz());
    if (!$dt || $dt <= now()) throw new DomainException('Некорректная дата (в прошлом).');
    if ($duration < 15 || $duration > 120) throw new DomainException('Длительность 15-120 мин.');

    $newSlot = ['id' => 's_' . bin2hex(random_bytes(4)), 'teacher_id' => $teacherId, 'date' => $date, 'time' => $time, 'duration' => $duration];
    modify_json(SLOTS_FILE, static fn(array $slots): array => array_merge($slots, [$newSlot]));
}

function get_teacher_slots(string $teacherId): array {
    $my = array_filter(load_all_slots(), fn($s) => $s['teacher_id'] === $teacherId);
    usort($my, fn($a, $b) => slot_start_datetime($a) <=> slot_start_datetime($b)); return $my;
}

function delete_slot(string $slotId, string $teacherId): void {
    modify_json(SLOTS_FILE, static function (array $slots) use ($slotId, $teacherId): array {
        foreach ($slots as $i => $s) {
            if ($s['id'] === $slotId) {
                if ($s['teacher_id'] !== $teacherId) throw new DomainException('Не ваш слот.');
                if (count(get_slot_bookings($slotId)) > 0) throw new DomainException('Нельзя удалить слот, на который уже есть записи.');
                unset($slots[$i]); return array_values($slots);
            }
        }
        throw new DomainException('Слот не найден.');
    });
}

function get_all_bookings_with_details(): array {
    $bookings = load_bookings(); $slots = load_all_slots(); $users = read_json_array(USERS_FILE); $rooms = get_all_rooms();
    $slotMap = []; foreach ($slots as $s) $slotMap[$s['id']] = $s;
    $userMap = []; foreach ($users as $u) $userMap[$u['id']] = $u;
    $roomMap = []; foreach ($rooms as $r) $roomMap[$r['id']] = $r['name'];

    $out = [];
    foreach ($bookings as $b) {
        $slot = $slotMap[$b['slot_id']] ?? null; $student = $userMap[$b['student_id']] ?? null;
        $teacherName = ($slot && isset($userMap[$slot['teacher_id']])) ? $userMap[$slot['teacher_id']]['name'] : 'Неизвестный';
        $out[] = [
            'status' => $b['status'], 'student_name' => $student ? $student['name'] : 'Удален',
            'student_email' => $student ? $student['email'] : '-', 'teacher_name' => $teacherName,
            'date' => $slot ? $slot['date'] : '', 'time' => $slot ? $slot['time'] : '', 'room_name' => $roomMap[$b['room_id']] ?? '?',
        ];
    }
    usort($out, fn($a, $b) => strcmp($b['date'].$b['time'], $a['date'].$a['time'])); return $out;
}