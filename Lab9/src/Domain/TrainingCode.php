<?php
declare(strict_types=1);

namespace App\Domain;

final readonly class TrainingCode
{
    public function __construct(public string $value)
    {
        if (!preg_match('/^[A-Z]{3}-\d{3}$/', $value)) {
            throw new \InvalidArgumentException('Формат кода тренировки: ТРИ буквы, дефис, три цифры.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
