<?php
declare(strict_types=1);

namespace App\Database;

use PDO;

final class ConnectionFactory
{
    public static function create(): PDO
    {
        // Для XAMPP стандартный хост — 127.0.0.1, имя пользователя — root, пароль — пустой
        $host = '127.0.0.1';
        $port = '3307'; // Если вы меняли порт в XAMPP на 3307, укажите здесь '3307'
        $dbname = 'php_library';
        $user = 'root'; 
        $password = ''; 

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname);

        return new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
