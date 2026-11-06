<?php
declare(strict_types=1);

// Говорим браузеру отображать страницу как обычный текст с правильной кодировкой
header('Content-Type: text/plain; charset=utf-8');

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Domain\{Student, Coach, Training, TrainingCode, User};

// Функция для демонстрации полиморфизма подтипов
function printUserCard(User $user): void
{
    printf(
        "Карточка #%d: %s | Категория: %s | Email: %s\n",
        $user->id,
        $user->fullName(),
        $user->roleLabel(),
        $user->email
    );
}

// Помощник для тестирования перехвата исключений
function expectException(callable $action, string $scenarioDescription): void
{
    try {
        $action();
        echo "FAIL❌: Сценарий [{$scenarioDescription}] не выбросил ожидаемое исключение.\n";
    } catch (\Throwable $e) {
        echo "OK  ✅: Сценарий [{$scenarioDescription}] успешно перехвачен. Ошибка: {$e->getMessage()}\n";
    }
}

echo "=== СТАРТ ТЕСТИРОВАНИЯ (ВАРИАНТ 9: СПОРТИВНЫЙ ЦЕНТР) ===\n\n";

// Сценарий 1: Успешное создание сущностей
try {
    $coach = new Coach(1, 'Иванов Петр', 'coach.ivanov@fit.com', 'CrossFit');
    $student1 = new Student(10, 'Алексей Смирнов', 'alex@mail.com', 'ST-2026-A');
    $student2 = new Student(11, 'Мария Иванова', 'maria@mail.com', 'ST-2026-B');
    echo "OK  ✅: Сценарий 1 — Корректные объекты Студентов и Тренера успешно созданы.\n";
} catch (\Throwable $e) {
    echo "FAIL❌: Сценарий 1 рухнул с ошибкой: " . $e->getMessage() . "\n";
}

// Сценарий 2: Тестирование полиморфного вывода карточек
echo "\n--- Демонстрация полиморфизма ---\n";
printUserCard($coach);
printUserCard($student1);
echo "---------------------------------\n\n";

// Сценарий 3: Создание корректной тренировки и успешная запись
try {
    $code = new TrainingCode('FIT-777');
    $time = new \DateTimeImmutable('2026-11-05 19:00:00');
    $training = new Training($code, 'Интенсивный CrossFit', $coach, $time, 2); // вместимость 2
    
    $training->book($student1);
    echo "OK  ✅: Сценарий 3 — Студент 1 успешно забронировал место. Свободно мест: " . $training->freePlaces() . "\n";
} catch (\Throwable $e) {
    echo "FAIL❌: Сценарий 3 рухнул.\n";
}

// Сценарий 4: Ошибка — Передача пустого имени
expectException(
    fn() => new Student(12, '  ', 'test@mail.com', 'ST-999'),
    'Создание студента с пустым именем'
);

// Сценарий 5: Ошибка — Неверный формат Email
expectException(
    fn() => new Coach(2, 'Василий Уткин', 'not-an-email', 'Бокс'),
    'Создание пользователя с некорректным Email'
);

// Сценарий 6: Ошибка — Неверный формат кода тренировки
expectException(
    fn() => new TrainingCode('INVALID123'),
    'Создание TrainingCode с нарушением регулярного выражения'
);

// Сценарий 7: Ошибка — Вместимость тренировки равна 0
expectException(
    fn() => new Training(new TrainingCode('BOX-101'), 'Бокс', $coach, new \DateTimeImmutable(), 0),
    'Создание тренировки с нулевой вместимостью'
);

// Сценарий 8: Ошибка — Повторная запись одного и того же студента
expectException(
    fn() => $training->book($student1),
    'Повторная запись студента на одну и ту же тренировку'
);

// Сценарий 9: Ошибка — Превышение лимита вместимости (Запись сверх лимита)
try {
    $training->book($student2); // Занимаем последнее 2-е место
    echo "OK  ✅: Студент 2 успешно занял последнее место.\n";
} catch (\Throwable $e) {
    echo "FAIL❌\n";
}

$student3 = new Student(15, 'Кирилл Кузнецов', 'kirill@mail.com', 'ST-555');
expectException(
    fn() => $training->book($student3),
    'Запись на тренировку при отсутствии свободных мест'
);

echo "\n=== ТЕСТИРОВАНИЕ ЗАВЕРШЕНО ===\n";
