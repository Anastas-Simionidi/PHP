<?php
declare(strict_types=1);

namespace App\Domain;

abstract class User
{
    public function __construct(
        public readonly int $id,
        private string $fullName,
        public readonly string $email,
    ) {
        if ($id < 1) {
            throw new \InvalidArgumentException('ID должен быть положительным.');
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Некорректный email.');
        }
        $this->changeName($fullName);
    }

    final public function fullName(): string
    {
        return $this->fullName;
    }

    final public function changeName(string $name): void
    {
        $name = trim($name);
        if (mb_strlen($name) < 3) {
            throw new \InvalidArgumentException('Имя слишком короткое.');
        }
        $this->fullName = $name;
    }

    abstract public function roleLabel(): string;
}
