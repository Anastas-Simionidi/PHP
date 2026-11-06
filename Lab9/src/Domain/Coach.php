<?php
declare(strict_types=1);

namespace App\Domain;

final class Coach extends User
{
    public function __construct(
        int $id,
        string $fullName,
        string $email,
        public readonly string $specialization,
    ) {
        parent::__construct($id, $fullName, $email);
        if (trim($specialization) === '') {
            throw new \InvalidArgumentException('Специализация обязательна.');
        }
    }

    #[\Override]
    public function roleLabel(): string
    {
        return 'Тренер (Специализация: ' . $this->specialization . ')';
    }
}
