# Сервис бронирования консультаций (Тема 19)

## Тема
Веб-приложение для записи студентов на консультации преподавателей по слотам.

## Цель
Закрепить навыки лекций 1–8: работа с массивами/JSON, формы и суперглобальные массивы,
валидация, sessions, flash-сообщения, CSRF, безопасная запись данных и PRG-паттерн.

## Функциональность
- список предстоящих слотов (прошедшие скрываются);
- валидируемая POST-форма бронирования;
- защита от двойного бронирования (атомарная проверка + `flock`);
- flash-подтверждение после успешной записи;
- личный список записей в сессии;
- отмена записи не позднее 60 минут до начала;
- журнал изменений (создание/отмена) в каждой записи;
- CSRF-защита всех изменяющих POST-запросов;
- экранирование всех динамических полей через `htmlspecialchars()`.

## Требования к среде
- PHP 8.4+ (модули `json`, `mbstring`);
- веб-сервер или встроенный сервер PHP;
- права на запись в `var/data/`.

## Установка и запуск
```bash
git clone <repo-url>
cd consultations
cp var/data/bookings.json.example var/data/bookings.json
php bin/setup.php        # генерирует слоты на 14 дней вперёд
php -S localhost:8000 -t public , 
( Сначала & "F:\XAMP\php\php.exe" bin/setup.php чтобы пересоздать базу данных ,& "F:\XAMP\php\php.exe" -S localhost:8000 -t public уже для запуска , выключить CTRL + S ) но это как один из вариантов.
```
Открыть: `http://localhost:8000/`.

Пересоздать слоты: `php bin/setup.php --force`.

## Структура каталогов
- `public/` — точка входа и статические ресурсы;
- `src/` — модули: хранилище, CSRF, flash, валидация, бизнес-логика;
- `templates/` — PHP-шаблоны представления;
- `var/data/` — JSON-файлы (вне публичного каталога);
- `bin/` — CLI-скрипты;
- `tests/` — сценарии ручного тестирования.

## Тестовые данные
- `bin/setup.php` создаёт ~56 слотов на 14 дней;
- демо-слоты можно забронировать, чтобы проверить состояния «Свободен / Занят».

## Рекомендуемая история коммитов (8+)
1. `chore: init project structure, .gitignore, README`
2. `feat: JSON storage with atomic writes`
3. `feat: slots list page with past filtering`
4. `feat: booking form with server-side validation`
5. `feat: flash messages and PRG redirects`
6. `feat: session-based "My bookings" page`
7. `feat: CSRF protection on POST endpoints`
8. `feat: cancel booking with time limit and history log`
9. `style: base CSS layout`
10. `docs: README, test-cases, bin/setup.php`

## Ограничения текущей версии
- идентификация пользователя по email из сессии (нет аутентификации);
- нет панели администратора для создания слотов (используется `bin/setup.php`);
- нет календарной сетки — только таблица слотов.

## Направления развития
- полноценная аутентификация (`password_hash`, `password_verify`);
- роль преподавателя и CRUD слотов;
- уведомления по email;
- переход на PDO при изучении СУБД.