<?php

declare(strict_types=1);

function tz(): DateTimeZone
{
    return new DateTimeZone(TZ_NAME);
}

function now(): DateTimeImmutable
{
    return new DateTimeImmutable('now', tz());
}

/** @param array<string,mixed> $slot */
function slot_start_datetime(array $slot): DateTimeImmutable
{
    $dt = DateTimeImmutable::createFromFormat(
        'Y-m-d H:i',
        (string) ($slot['date'] ?? '') . ' ' . (string) ($slot['time'] ?? ''),
        tz()
    );
    if ($dt === false) {
        throw new RuntimeException('Некорректные дата/время слота.');
    }
    return $dt;
}

/** @return array<int,array<string,mixed>> */
function load_all_slots(): array
{
    $slots = read_json_array(SLOTS_FILE);
    return array_values(array_filter($slots, static fn($s) => is_array($s) && isset($s['id'])));
}

/** @return array<string,mixed>|null */
function find_slot(string $slotId): ?array
{
    foreach (load_all_slots() as $slot) {
        if (($slot['id'] ?? null) === $slotId) {
            return $slot;
        }
    }
    return null;
}

/** @return array<int,array<string,mixed>> */
function load_bookings(): array
{
    $bookings = read_json_array(BOOKINGS_FILE);
    return array_values(array_filter($bookings, static fn($b) => is_array($b) && isset($b['id'])));
}

/** @param array<int,array<string,mixed>> $bookings */
function active_booking_for_slot(array $bookings, string $slotId): ?array
{
    foreach ($bookings as $b) {
        if (($b['slot_id'] ?? null) === $slotId && ($b['status'] ?? '') === 'active') {
            return $b;
        }
    }
    return null;
}

/**
 * @param array{student_name:string,email:string,comment:string,slot_id:string} $data
 * @return array<string,mixed>
 */
function book_slot(string $slotId, array $data): array
{
    $slot = find_slot($slotId);
    if ($slot === null) {
        throw new DomainException('Слот не найден.');
    }
    if (slot_start_datetime($slot) <= now()) {
        throw new DomainException('Этот слот уже в прошлом.');
    }

    $ts = now()->format(DateTimeInterface::ATOM);
    $booking = [
        'id'           => 'b_' . bin2hex(random_bytes(6)),
        'slot_id'      => $slotId,
        'student_name' => $data['student_name'],
        'email'        => $data['email'],
        'comment'      => $data['comment'],
        'status'       => 'active',
        'created_at'   => $ts,
        'history'      => [
            ['action' => 'created', 'at' => $ts],
        ],
    ];

    modify_json(BOOKINGS_FILE, static function (array $bookings) use ($slotId, $booking): array {
        if (active_booking_for_slot($bookings, $slotId) !== null) {
            throw new DomainException('Этот слот уже занят.');
        }
        $bookings[] = $booking;
        return $bookings;
    });

    return $booking;
}

/** @return array<string,mixed> */
function cancel_booking(string $bookingId, string $email): array
{
    $result = null;
    modify_json(BOOKINGS_FILE, static function (array $bookings) use ($bookingId, $email, &$result): array {
        foreach ($bookings as $i => $b) {
            if (($b['id'] ?? null) !== $bookingId) {
                continue;
            }
            if (($b['email'] ?? '') !== $email) {
                throw new DomainException('Это не ваша запись.');
            }
            if (($b['status'] ?? '') !== 'active') {
                throw new DomainException('Запись уже отменена.');
            }
            $slot = find_slot((string) $b['slot_id']);
            if ($slot === null) {
                throw new DomainException('Слот записи не найден.');
            }
            $deadline = slot_start_datetime($slot)
                ->sub(new DateInterval('PT' . CANCEL_MINUTES_BEFORE . 'M'));
            if (now() >= $deadline) {
                throw new DomainException(
                    'Отмена возможна не позднее чем за ' . CANCEL_MINUTES_BEFORE . ' минут до начала.'
                );
            }
            $bookings[$i]['status']  = 'cancelled';
            $bookings[$i]['history'][] = ['action' => 'cancelled', 'at' => now()->format(DateTimeInterface::ATOM)];
            $result = $bookings[$i];
            return $bookings;
        }
        throw new DomainException('Запись не найдена.');
    });

    /** @var array<string,mixed> $result */
    return $result;
}

/**
 * @return array<int,array{slot:array<string,mixed>,start:DateTimeImmutable,is_booked:bool}>
 */
function upcoming_slots_with_status(): array
{
    $slots    = load_all_slots();
    $bookings = load_bookings();
    $n        = now();
    $out      = [];

    foreach ($slots as $slot) {
        $start = slot_start_datetime($slot);
        if ($start <= $n) {
            continue; // прошедшие интервалы скрыты
        }
        $out[] = [
            'slot'      => $slot,
            'start'     => $start,
            'is_booked' => active_booking_for_slot($bookings, (string) $slot['id']) !== null,
        ];
    }

    usort($out, static fn(array $a, array $b) => $a['start'] <=> $b['start']);
    return $out;
}

/**
 * @return array<int,array<string,mixed>>
 */
function own_bookings(string $email): array
{
    $out = [];
    foreach (load_bookings() as $b) {
        if (($b['email'] ?? '') !== $email) {
            continue;
        }
        $slot = find_slot((string) ($b['slot_id'] ?? ''));
        if ($slot === null) {
            continue;
        }
        $b['slot']  = $slot;
        $b['start'] = slot_start_datetime($slot);
        $out[]      = $b;
    }
    usort($out, static fn(array $a, array $b) => $b['start'] <=> $a['start']);
    return $out;
}

function remember_email(string $email): void
{
    $_SESSION['email'] = $email;
}

function current_email(): ?string
{
    return isset($_SESSION['email']) && is_string($_SESSION['email'])
        ? $_SESSION['email']
        : null;
}