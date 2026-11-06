<?php
declare(strict_types=1);

namespace App\Domain;

final class Student extends User
{
    public function __construct(
        int $id,
        string $fullName,
        string $email,
        public readonly string $membershipNumber,
    ) {
        parent::__construct($id, $fullName, $email);
        if (trim($membershipNumber) === '') {
            throw new \InvalidArgumentException('Номер абонемента обязателен.');
        }
    }

    #[\Override]
    public function roleLabel(): string
    {
        return 'Студент (Абонемент: ' . $this->membershipNumber . ')';
    }
}
