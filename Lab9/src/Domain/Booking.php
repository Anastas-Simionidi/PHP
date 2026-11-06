<?php
declare(strict_types=1);

namespace App\Domain;

final readonly class Booking
{
    public function __construct(
        public int $studentId,
        public TrainingCode $trainingCode,
        public \DateTimeImmutable $bookedAt,
    ) {
        if ($studentId < 1) {
            throw new \InvalidArgumentException('Некорректный ID студента.');
        }
    }
}
