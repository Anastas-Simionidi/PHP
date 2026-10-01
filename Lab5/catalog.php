<?php
declare(strict_types=1);

// Вариант 16: Музыкальные инструменты
// Дополнительные поля: type, manufacturer
$products = [
    [
        "id" => 1,
        "name" => " Гитара  акустическая F310 ",
        "category" => "Гитары",
        "price" => 85000.0,
        "stock" => 10,
        "created_at" => "2026-01-15",
        "type" => "Акустическая гитара",
        "manufacturer" => "Yamaha"
    ],
    [
        "id" => 2,
        "name" => "Электрогитара Pacifica 112V",
        "category" => "Гитары",
        "price" => 175000.0,
        "stock" => 4,
        "created_at" => "2026-02-10",
        "type" => "Электрогитара",
        "manufacturer" => "Yamaha"
    ],
    [
        "id" => 3,
        "name" => "Цифровое пианино P-45",
        "category" => "Клавишные",
        "price" => 290000.0,
        "stock" => 2,
        "created_at" => "2026-03-01",
        "type" => "Цифровое пианино",
        "manufacturer" => "Yamaha"
    ],
    [
        "id" => 4,
        "name" => "Синтезатор CT-X700",
        "category" => "Клавишные",
        "price" => 110000.0,
        "stock" => 0, // Нет в наличии
        "created_at" => "2026-03-12",
        "type" => "Синтезатор",
        "manufacturer" => "Casio"
    ],
    [
        "id" => 5,
        "name" => "Бас-гитара <script>alert('xss')</script> GSR200",
        "category" => "Гитары",
        "price" => 140000.0,
        "stock" => 5,
        "created_at" => "2026-04-05",
        "type" => "Бас-гитара",
        "manufacturer" => "Ibanez"
    ],
    [
        "id" => 6,
        "name" => "Ударная установка Rhythm Mate",
        "category" => "Ударные",
        "price" => 350000.0,
        "stock" => 1,
        "created_at" => "2026-05-20",
        "type" => "Акустические барабаны",
        "manufacturer" => "TAMA"
    ],
    [
        "id" => 7,
        "name" => "Синтезатор Minilogue XD",
        "category" => "Клавишные",
        "price" => 310000.0,
        "stock" => 3,
        "created_at" => "2026-06-11",
        "type" => "Аналоговый синтезатор",
        "manufacturer" => "Korg"
    ],
    [
        "id" => 8,
        "name" => "Укулеле UK-21",
        "category" => "Гитары",
        "price" => 18000.0,
        "stock" => 15,
        "created_at" => "2026-07-01",
        "type" => "Сопрано укулеле",
        "manufacturer" => "Flight"
    ]
];