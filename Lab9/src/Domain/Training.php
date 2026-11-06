<?php
declare(strict_types=1);

namespace App\Domain;

final class Training
{
    /** @var list<Booking> */
    private array $bookings = [];

    public function __construct(
        public readonly TrainingCode $code,
        public readonly string $title,
        public readonly Coach $coach,
        public readonly \DateTimeImmutable $startTime,
        private int $capacity,
    ) {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Название тренировки обязательно.');
        }
        if ($capacity < 1) {
            throw new \InvalidArgumentException('Вместимость должна быть положительной.');
        }
    }

    public function book(Student $student): Booking
    {
        if ($this->isFull()) {
            throw new \DomainException('На тренировке нет свободных мест.');
        }

        foreach ($this->bookings as $booking) {
            if ($booking->studentId === $student->id) {
                throw new \DomainException('Студент уже записан на эту тренировку.');
            }
        }

        $booking = new Booking($student->id, $this->code, new \DateTimeImmutable());
        $this->bookings[] = $booking;
        return $booking;
    }

    public function isFull(): bool
    {
        return count($this->bookings) >= $this->capacity;
    }

    public function freePlaces(): int
    {
        return $this->capacity - count($this->bookings);
    }

    /** @return list<Booking> */
    public function bookings(): array
    {
        return $this->bookings;
    }
}
